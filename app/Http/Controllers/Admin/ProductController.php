<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariantImage;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Attribute;
use App\Models\Color;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\ImageHelper;

class ProductController extends Controller
{
    /**
     * Display products list with advanced filtering.
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        // Determine if this is for in-house products or all products
        // Check both route parameter (for backward compatibility) and route name
        $isInHouse = $request->get('view') === 'in-house' || $request->routeIs('products.in-house');

        // Start with base query - always filter non-digital for physical product listings
        $query = Product::with('category')->where('is_digital', false);

        // Apply in-house filter if viewing in-house products
        if ($isInHouse) {
            $query->inHouse();
        }

        // Search by name, SKU, Product Code, or description
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        // Filter by status
        if ($request->status !== null && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by stock status (using dynamic low_stock_threshold)
        if ($request->stock_status) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->whereRaw('quantity > COALESCE(low_stock_threshold, 10)');
                    break;
                case 'low_stock':
                    $query->whereRaw('quantity > 0 AND quantity <= COALESCE(low_stock_threshold, 10)');
                    break;
                case 'out_of_stock':
                    $query->where('quantity', '<=', 0);
                    break;
            }
        }

        // Filter by featured status
        if ($request->featured !== null && $request->featured !== '') {
            $query->where('is_featured', $request->featured === 'yes');
        }

        // Filter by price range
        if ($request->price_min) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->price_max) {
            $query->where('price', '<=', $request->price_max);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting
        $sortField = $request->sort ?? 'created_at';
        $sortDirection = $request->direction ?? 'desc';

        $allowedSorts = ['name', 'price', 'quantity', 'created_at', 'sale_price', 'purchase_price', 'low_stock_threshold'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }

        // Per page
        $perPage = $request->per_page ?? 25;

        $products = $query->paginate($perPage)->appends($request->query());
        $categories = Category::where('status', 'active')->get();

        // Get unique brands for filter
        $brandQuery = $isInHouse ? Product::inHouse() : Product::query();
        $brands = $brandQuery
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->pluck('brand')
            ->sort()
            ->values();

        // Get brands from Brand model for dropdown
        $brandModels = Brand::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');

        // Convert to array for dropdown
        $brands = $brandModels->toArray();

        // Statistics - use appropriate scope based on list type
        $statsQuery = $isInHouse ? Product::inHouse() : Product::query();
        $stats = [
            'total' => $statsQuery->count(),
            'active' => $statsQuery->where('is_active', true)->count(),
            'inactive' => $statsQuery->where('is_active', false)->count(),
            'featured' => $statsQuery->where('is_featured', true)->count(),
            'low_stock' => $statsQuery
                ->where('quantity', '>', 0)
                ->whereRaw('quantity <= COALESCE(low_stock_threshold, 10)')
                ->count(),
            'out_of_stock' => $statsQuery->where('quantity', '<=', 0)->count(),
            'total_stock_value' => $statsQuery->sum(DB::raw('quantity * COALESCE(purchase_price, cost_price, 0)')),
            'total_retail_value' => $statsQuery->sum(DB::raw('quantity * COALESCE(sale_price, price, 0)')),
            'total_quantity' => $statsQuery->sum('quantity'),
        ];

        // Return JSON for AJAX requests
        if ($request->ajax || $request->ajax == '1' || $request->wantsJson()) {
            $html = view('admin.products.partials.in-house-product-rows', compact('products'))->render();

            $pagination = '';
            if ($products->hasPages()) {
                $pagination = '<div class="d-flex justify-content-center mt-3">' . $products->links()->toHtml() . '</div>';
            }

            return response()->json([
                'html' => $html,
                'pagination' => $pagination,
                'stats' => $stats,
                'total' => $products->total()
            ]);
        }

        // Return appropriate view based on list type
        if ($isInHouse) {
            return view('admin.products.in-house', compact('products', 'categories', 'brands', 'stats'));
        }

        return view('admin.products.index', compact('products', 'categories', 'brands', 'stats'));
    }

    /**
     * Show create form.
     */
    public function create(Request $request)
    {
        $categories = Category::getFlattenedTree();
        $preselectedCategory = $request->get('category_id');
        
        // Get active brands for dropdown
        $brands = Brand::where('is_active', true)->orderBy('name')->pluck('name', 'id')->toArray();
        
        // Get active attributes with their values
        $attributes = Attribute::with('activeValues')->where('is_active', true)->get();
        
        // Get active colors with their values
        $colors = Color::with('activeValues')->where('is_active', true)->get();
        
        // Generate SKU based on timestamp for uniqueness
        $nextSku = 'SKU-' . date('YmdHis');
        
        // Get next unique SKU for attributes/colors
        $nextUniqueSku = $this->getNextUniqueSku();
        
        // Determine redirect route based on source
        $redirectRoute = 'admin.products.index';
        if ($request->has('from') && $request->from === 'in-house') {
            $redirectRoute = 'admin.products.in-house';
        } elseif (str_contains($request->headers->get('referer', ''), 'in-house')) {
            $redirectRoute = 'admin.products.in-house';
        }
        
        // Store the redirect route in session for use after form submission
        session(['product_redirect_route' => $redirectRoute]);
        
        return view('admin.products.create', compact('categories', 'preselectedCategory', 'nextSku', 'nextUniqueSku', 'redirectRoute', 'brands', 'attributes', 'colors'));
    }

    /**
     * Store new product.
     */
    public function store(Request $request)
    {
        Log::info('Product store request data: ', $request->all());

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    $price = $request->input('price', 0);
                    if ($value !== null && $value > 0 && $value > $price) {
                        $fail('Sale Price cannot be higher than Regular Price (' . $price . ').');
                    }
                },
            ],
            'purchase_price' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:100',
            'product_code' => 'required|string|max:100|unique:products,product_code',
            'barcode' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'stock' => [
                'required',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    $lowStockThreshold = $request->input('low_stock_threshold', 0);
                    if ($value < $lowStockThreshold && $lowStockThreshold > 0) {
                        $fail('Stock Quantity must be greater than or equal to Low Stock Alert (' . $lowStockThreshold . ').');
                    }
                },
            ],
            'low_stock_threshold' => 'nullable|integer|min:0',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'specs' => 'nullable|array',
            'specs.*.key' => 'nullable|string|max:255',
            'specs.*.value' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:5120',
            'images.*' => 'nullable|image|max:5120',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Check for duplicate product code before saving
        if (\App\Models\Product::where('product_code', $request->product_code)->exists()) {
            return back()->withInput()->with('error', 'Product code "' . $request->product_code . '" already exists. Please use a different product code.');
        }
        
        $data = $request->except(['description', 'stock']);
        
        // Handle brand selection - get brand name and ID from selected brand
        if ($request->has('brand') && $request->brand != '') {
            $brandId = $request->input('brand');
            $brand = \App\Models\Brand::find($brandId);
            if ($brand) {
                $data['brand'] = $brand->name;
                $data['brand_id'] = $brand->id;
            } else {
                // If brand not found, treat as new brand name
                $data['brand'] = $request->input('brand');
                $data['brand_id'] = null;
            }
        } else {
            $data['brand'] = $request->input('brand', '');
            $data['brand_id'] = null;
        }
        
        $data['slug'] = Str::slug($request->name);
        
        // Ensure slug is unique - append number if exists
        $originalSlug = $data['slug'];
        $counter = 1;
        while (\App\Models\Product::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
            if ($counter > 100) break;
        }
        
        $data['long_description'] = $request->description;
        $data['quantity'] = $request->stock;
        
        // Use submitted SKU if provided, otherwise auto-generate
        $data['sku'] = $request->sku ?? 'SKU-' . date('YmdHis');
        
        // Set as in-house product by default
        $data['product_source'] = 'in_house';
        $data['low_stock_threshold'] = $request->low_stock_threshold ?? 10;
        $data['stock_update_date'] = now();
        $data['weight'] = $request->weight ?: null;
        $data['dimensions'] = $request->dimensions ?: null;

        // Process featured image with WebP conversion, resize & thumbnail
        if ($request->hasFile('image')) {
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'products',      // directory
                    1920,            // max width
                    300,             // thumbnail width
                    85               // quality
                );
                $data['featured_image'] = $imageResult['path'];
                $data['thumbnail'] = $imageResult['thumbnail'] ?? null;
            }
        }

        // Process gallery images
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                if (ImageHelper::isValidImage($image)) {
                    $imageResult = ImageHelper::processImage($image, 'products/gallery', 1200, 0, 85);
                    $images[] = $imageResult['path'];
                }
            }
            $data['images'] = json_encode($images);
        }

        $product = Product::create($data);
        
        // Save product attributes
        if ($request->has('product_attributes')) {
            $attributesData = [];
            foreach ($request->product_attributes as $attrId => $attrData) {
                if (isset($attrData['values'])) {
                    $values = [];
                    foreach ($attrData['values'] as $valueId => $valueData) {
                        $values[$valueId] = [
                            'value_id' => $valueData['value_id'] ?? $valueId,
                            'value_name' => $valueData['value_name'] ?? '',
                            'price' => floatval($valueData['price'] ?? 0),
                            'quantity' => intval($valueData['quantity'] ?? 0),
                            'sku' => $valueData['sku'] ?? '',
                            'image' => null,
                            'is_visible' => isset($valueData['is_visible']) ? filter_var($valueData['is_visible'], FILTER_VALIDATE_BOOLEAN) : true,
                        ];
                        
                        // Handle image upload with WebP conversion
                        if (isset($valueData['image']) && $valueData['image'] instanceof \Illuminate\Http\UploadedFile) {
                            if (ImageHelper::isValidImage($valueData['image'])) {
                                $imageResult = ImageHelper::processImage($valueData['image'], 'products/attributes', 600, 0, 85);
                                $values[$valueId]['image'] = $imageResult['path'];
                            }
                        }
                    }
                    $attributesData[$attrId] = [
                        'name' => $attrData['name'] ?? '',
                        'values' => $values
                    ];
                }
            }
            if (!empty($attributesData)) {
                $product->attributes = json_encode($attributesData);
                $product->save();
            }
        }
        
        // Save product colors
        if ($request->has('product_colors')) {
            $colorsData = [];
            foreach ($request->product_colors as $colorId => $colorData) {
                $colorItem = [
                    'color_id' => $colorId,
                    'price' => floatval($colorData['price'] ?? 0),
                    'quantity' => intval($colorData['quantity'] ?? 0),
                    'sku' => $colorData['sku'] ?? '',
                    'image' => null,
                ];
                
                // Handle image upload with WebP conversion
                if (isset($colorData['image']) && $colorData['image'] instanceof \Illuminate\Http\UploadedFile) {
                    if (ImageHelper::isValidImage($colorData['image'])) {
                        $imageResult = ImageHelper::processImage($colorData['image'], 'products/colors', 600, 0, 85);
                        $colorItem['image'] = $imageResult['path'];
                    }
                }
                
                // Handle color values (like sizes) with their images
                $valuesData = [];
                if (isset($colorData['values']) && is_array($colorData['values'])) {
                    foreach ($colorData['values'] as $valueId => $valueData) {
                        // Get hex_code from ColorValue model
                        $hexCode = $valueData['hex_code'] ?? null;
                        if (!$hexCode) {
                            $colorValue = \App\Models\ColorValue::find($valueId);
                            if ($colorValue) {
                                $hexCode = $colorValue->hex_code;
                            }
                        }
                        // Fallback to color's hex_code
                        if (!$hexCode) {
                            $color = \App\Models\Color::find($colorId);
                            if ($color) {
                                $hexCode = $color->hex_code;
                            }
                        }
                        $valueItem = [
                            'value_id' => $valueData['value_id'] ?? $valueId,
                            'value_name' => $valueData['value_name'] ?? '',
                            'hex_code' => $hexCode ?? '#000000',
                            'price' => floatval($valueData['price'] ?? 0),
                            'quantity' => intval($valueData['quantity'] ?? 0),
                            'sku' => $valueData['sku'] ?? '',
                            'image' => null,
                            'is_visible' => isset($valueData['is_visible']) ? filter_var($valueData['is_visible'], FILTER_VALIDATE_BOOLEAN) : true,
                        ];
                        
                        // Handle value image upload with WebP conversion
                        if (isset($valueData['image']) && $valueData['image'] instanceof \Illuminate\Http\UploadedFile) {
                            if (ImageHelper::isValidImage($valueData['image'])) {
                                $imageResult = ImageHelper::processImage($valueData['image'], 'products/colors', 600, 0, 85);
                                $valueItem['image'] = $imageResult['path'];
                            }
                        }
                        
                        $valuesData[$valueId] = $valueItem;
                    }
                }
                
                if (!empty($valuesData)) {
                    $colorItem['values'] = $valuesData;
                }
                
                $colorsData[] = $colorItem;
            }
            if (!empty($colorsData)) {
                $product->colors = json_encode($colorsData);
                $product->save();
            }
        }
        
        // Handle variant images (combination images)
        $this->saveVariantImages($product, $request);
        
        $redirectRoute = $request->redirect_route ?? session('product_redirect_route', 'admin.products.index');
        
        return redirect()->route($redirectRoute)->with('success', 'Product created successfully.');
    }

    /**
     * Show product details.
     */
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show edit form.
     */
    public function edit(Product $product)
    {
        // If product is null (soft-deleted), try to find it with trashed
        if (!$product) {
            $product = Product::withTrashed()->find(request()->route('product'));
            if ($product && $product->trashed()) {
                $product->restore();
            }
        }
        
        Log::info('EDIT METHOD REACHED', ['product_id' => $product->id]);
        $categories = Category::getFlattenedTree();
        $brands = Brand::where('is_active', true)->orderBy('name')->pluck('name', 'id')->toArray();
        
        $attributes = Attribute::with('activeValues')->where('is_active', true)->get();
        
        // Prepare attributes data for JavaScript
        $attributesData = $attributes->map(function($attr) {
            return [
                'id' => $attr->id,
                'name' => $attr->name,
                'values' => $attr->activeValues->map(function($val) {
                    return ['id' => $val->id, 'value' => $val->value];
                })->toArray()
            ];
        })->toArray();
        
        $colors = Color::with('activeValues')->where('is_active', true)->get();
        
        // Prepare colors data for JavaScript
        $colorsData = $colors->map(function($color) {
            return [
                'id' => $color->id,
                'name' => $color->name,
                'hex_code' => $color->hex_code,
                'values' => $color->activeValues->map(function($v) {
                    return [
                        'id' => $v->id,
                        'value' => $v->value,
                        'hex_code' => $v->hex_code
                    ];
                })->toArray()
            ];
        })->toArray();
        
        // Get existing product attributes
        $existingAttributes = [];
        if ($product->attributes) {
            $productAttrs = is_array($product->attributes) ? $product->attributes : json_decode($product->attributes, true);
            if ($productAttrs) {
                foreach ($productAttrs as $attrId => $attrData) {
                    $existingAttributes[$attrId] = [
                        'name' => $attrData['name'] ?? '',
                        'values' => $attrData['values'] ?? []
                    ];
                }
            }
        }
        
        // Get existing product colors
        $existingColors = [];
        if ($product->colors) {
            $productColors = is_array($product->colors) ? $product->colors : json_decode($product->colors, true);
            if ($productColors) {
                foreach ($productColors as $colorData) {
                    $existingColors[] = $colorData;
                }
            }
        }
        
        // Get existing variant images
        $variantImagesData = [];
        $existingVariantImages = ProductVariantImage::where('product_id', $product->id)->get();
        foreach ($existingVariantImages as $vi) {
            $variantImagesData[$vi->combination_key] = [
                'image' => $vi->image,
            ];
        }
        
        return view('admin.products.edit', compact('product', 'categories', 'brands', 'attributes', 'attributesData', 'colors', 'colorsData', 'existingAttributes', 'existingColors', 'variantImagesData'));
    }

    /**
     * Update product.
     */
    public function update(Request $request, Product $product)
    {
        // If product is null (soft-deleted), try to find it with trashed
        if (!$product || !$product->exists) {
            $product = Product::withTrashed()->find($request->route('product'));
            if ($product && $product->trashed()) {
                $product->restore();
            }
        }
        
        if (!$product) {
            return redirect()->route('admin.products.index')->with('error', 'Product not found.');
        }
        
        // Debug: Log the incoming request
        Log::info('Update product START', ['product_id' => $product->id, 'route' => $request->path()]);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    $price = $request->input('price', 0);
                    if ($value !== null && $value > 0 && $value > $price) {
                        $fail('Sale Price cannot be higher than Regular Price (' . $price . ').');
                    }
                },
            ],
            'purchase_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|max:100|unique:products,sku,' . $product->id,
            'product_code' => 'nullable|string|max:100|unique:products,product_code,' . $product->id,
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'brand' => 'nullable|string|max:100',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'weight' => 'nullable|numeric|min:0',
            'dimensions' => 'nullable|string|max:100',
            'specs' => 'nullable|array',
            'specs.*.key' => 'nullable|string|max:255',
            'specs.*.value' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:5120',
            'images.*' => 'nullable|image|max:5120',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $data = $request->except(['description', 'stock', 'brand']);
        
        // Handle brand selection - get brand name and ID from selected brand
        if ($request->has('brand') && $request->brand != '') {
            $brandId = $request->input('brand');
            $brand = \App\Models\Brand::find($brandId);
            if ($brand) {
                $data['brand'] = $brand->name;
                $data['brand_id'] = $brand->id;
            } else {
                $data['brand'] = $request->input('brand');
                $data['brand_id'] = null;
            }
        } else {
            $data['brand'] = null;
            $data['brand_id'] = null;
        }
        
        $data['slug'] = Str::slug($request->name);
        $data['long_description'] = $request->description;
        $data['quantity'] = $request->stock;
        $data['low_stock_threshold'] = $request->low_stock_threshold ?? $product->low_stock_threshold ?? 10;
        $data['weight'] = $request->weight ?: null;
        $data['dimensions'] = $request->dimensions ?: null;
        
        // Track stock updates
        if ($request->stock != $product->quantity) {
            $data['stock_update_date'] = now();
        }

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->featured_image) {
                ImageHelper::deleteImage($product->featured_image, $product->thumbnail ?? null);
            }
            
            // Process featured image with WebP conversion, resize & thumbnail
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'products',      // directory
                    1920,            // max width
                    300,             // thumbnail width
                    85               // quality
                );
                $data['featured_image'] = $imageResult['path'];
                $data['thumbnail'] = $imageResult['thumbnail'] ?? null;
            }
        }

        // Handle deleted gallery images
        $images = $product->images ?? [];
        if ($request->has('deleted_image_indices')) {
            $deletedIndices = $request->deleted_image_indices;
            // Sort in descending order to remove from end first (preserve indices)
            rsort($deletedIndices);
            foreach ($deletedIndices as $index) {
                if (isset($images[$index])) {
                    // Delete the file from storage using ImageHelper
                    ImageHelper::deleteImage($images[$index]);
                    unset($images[$index]);
                }
            }
            // Re-index array
            $images = array_values($images);
            $data['images'] = json_encode($images);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Process gallery image with WebP conversion
                if (ImageHelper::isValidImage($image)) {
                    $imageResult = ImageHelper::processImage($image, 'products/gallery', 1200, 0, 85);
                    $images[] = $imageResult['path'];
                }
            }
            $data['images'] = json_encode($images);
        }

        $product->update($data);
        
        // Save product attributes
        if ($request->has('product_attributes')) {
            $attributesData = [];
            foreach ($request->product_attributes as $attrId => $attrData) {
                if (isset($attrData['values'])) {
                    $values = [];
                    foreach ($attrData['values'] as $valueId => $valueData) {
                        $values[$valueId] = [
                            'value_id' => $valueData['value_id'] ?? $valueId,
                            'value_name' => $valueData['value_name'] ?? '',
                            'price' => floatval($valueData['price'] ?? 0),
                            'quantity' => intval($valueData['quantity'] ?? 0),
                            'sku' => $valueData['sku'] ?? '',
                            'image' => $valueData['existing_image'] ?? null,
                            'is_visible' => isset($valueData['is_visible']) ? filter_var($valueData['is_visible'], FILTER_VALIDATE_BOOLEAN) : true,
                        ];
                        
                        // Handle new image upload
                        if (isset($valueData['image']) && $valueData['image'] instanceof \Illuminate\Http\UploadedFile) {
                            if (ImageHelper::isValidImage($valueData['image'])) {
                                $imageResult = ImageHelper::processImage($valueData['image'], 'products/attributes', 600, 0, 85);
                                $values[$valueId]['image'] = $imageResult['path'];
                            }
                        }
                    }
                    $attributesData[$attrId] = [
                        'name' => $attrData['name'] ?? '',
                        'values' => $values
                    ];
                }
            }
            if (!empty($attributesData)) {
                $product->attributes = json_encode($attributesData);
                $product->save();
            }
        }
        
        // Handle attribute deletion: remove attributes that were in DB but not in submitted data
        $existingAttributes = json_decode($product->attributes ?? '{}', true);
        if (!empty($existingAttributes)) {
            $submittedAttrIds = $request->has('product_attributes') ? array_keys($request->product_attributes) : [];
            $attrsToDelete = array_diff(array_keys($existingAttributes), $submittedAttrIds);
            if (!empty($attrsToDelete)) {
                foreach ($attrsToDelete as $attrId) {
                    if (isset($existingAttributes[$attrId]) && isset($existingAttributes[$attrId]['values'])) {
                        foreach ($existingAttributes[$attrId]['values'] as $value) {
                            if (!empty($value['image'])) {
                                ImageHelper::deleteImage($value['image']);
                            }
                        }
                    }
                }
                $filteredAttrs = array_filter($existingAttributes, function($key) use ($attrsToDelete) {
                    return !in_array($key, $attrsToDelete);
                }, ARRAY_FILTER_USE_KEY);
                $product->attributes = json_encode($filteredAttrs);
                $product->save();
            }
        }
        
        // If product_attributes submitted but empty, clear all attributes
        if ($request->has('product_attributes') && empty($request->product_attributes)) {
            $product->attributes = json_encode([]);
            $product->save();
        }
        
        // Save product colors
        $submittedColorIds = [];
        if ($request->has('product_colors')) {
            $colorsData = [];
            foreach ($request->product_colors as $colorId => $colorData) {
                $colorItem = [
                    'color_id' => $colorId,
                    'price' => floatval($colorData['price'] ?? 0),
                    'quantity' => intval($colorData['quantity'] ?? 0),
                    'sku' => $colorData['sku'] ?? '',
                    'image' => $colorData['existing_image'] ?? null,
                ];

                $submittedColorIds[] = $colorId;

                // Handle new image upload with WebP conversion (old format)
                if (isset($colorData['image']) && $colorData['image'] instanceof \Illuminate\Http\UploadedFile) {
                    if (ImageHelper::isValidImage($colorData['image'])) {
                        $imageResult = ImageHelper::processImage($colorData['image'], 'products/colors', 600, 0, 85);
                        $colorItem['image'] = $imageResult['path'];
                    }
                }

                // Handle color values images (new format)
                if (isset($colorData['values']) && is_array($colorData['values'])) {
                    $colorItem['values'] = [];
                    foreach ($colorData['values'] as $valueId => $valueData) {
                        $valueItem = [
                            'value_id' => $valueData['value_id'] ?? $valueId,
                            'value_name' => $valueData['value_name'] ?? '',
                            'hex_code' => $valueData['hex_code'] ?? null,
                            'price' => floatval($valueData['price'] ?? 0),
                            'quantity' => intval($valueData['quantity'] ?? 0),
                            'sku' => $valueData['sku'] ?? '',
                            'image' => $valueData['existing_image'] ?? null,
                            'is_visible' => isset($valueData['is_visible']) ? filter_var($valueData['is_visible'], FILTER_VALIDATE_BOOLEAN) : true,
                        ];

                        // Handle new image upload for value
                        if (isset($valueData['image']) && $valueData['image'] instanceof \Illuminate\Http\UploadedFile) {
                            if (ImageHelper::isValidImage($valueData['image'])) {
                                $imageResult = ImageHelper::processImage($valueData['image'], 'products/colors', 600, 0, 85);
                                $valueItem['image'] = $imageResult['path'];
                            }
                        }

                        $colorItem['values'][$valueId] = $valueItem;
                    }
                }

                $colorsData[] = $colorItem;
            }
            if (!empty($colorsData)) {
                $product->colors = json_encode($colorsData);
                $product->save();
            }
        }

        // Handle color deletion: remove colors that were in DB but not in submitted data
        $existingColors = json_decode($product->colors ?? '[]', true);
        if (!empty($existingColors)) {
            $existingColorIds = array_column($existingColors, 'color_id');
            $colorsToDelete = array_diff($existingColorIds, $submittedColorIds);
            if (!empty($colorsToDelete)) {
                $colorsData = array_filter($existingColors, function($color) use ($colorsToDelete) {
                    return !in_array($color['color_id'], $colorsToDelete);
                });
                $product->colors = json_encode(array_values($colorsData));
                $product->save();
            }
        }

        // If product_colors submitted but empty, clear all colors
        if ($request->has('product_colors') && empty($request->product_colors)) {
            $product->colors = json_encode([]);
            $product->save();
        }
        
        // Handle variant images (combination images)
        $this->saveVariantImages($product, $request);
        
        // Debug: Log success
        Log::info('Product updated successfully', ['product_id' => $product->id]);

        return redirect()->back()->with('success', 'Product updated successfully.');
    }

    /**
     * Delete product.
     */
    public function destroy(Product $product)
    {
        // Delete main image
        if ($product->featured_image) {
            ImageHelper::deleteImage($product->featured_image, $product->thumbnail ?? null);
        }
        
        // Delete gallery images
        $images = $product->images ?? [];
        foreach ($images as $image) {
            ImageHelper::deleteImage($image);
        }
        
        $product->forceDelete();
        return back()->with('success', 'Product deleted successfully.');
    }

    /**
     * Bulk action with enhanced options.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
        ]);

        $action = $request->action;
        $ids = $request->ids;
        $count = count($ids);

        switch ($action) {
            case 'delete':
                // Delete images for each product
                $products = Product::whereIn('id', $ids)->get();
                foreach ($products as $product) {
                    if ($product->featured_image) {
                        ImageHelper::deleteImage($product->featured_image, $product->thumbnail ?? null);
                    }
                    $images = $product->images ?? [];
                    foreach ($images as $image) {
                        ImageHelper::deleteImage($image);
                    }
                }
                Product::whereIn('id', $ids)->forceDelete();
                $message = "{$count} product(s) deleted successfully.";
                break;

            case 'activate':
                Product::whereIn('id', $ids)->update(['is_active' => true]);
                $message = "{$count} product(s) activated successfully.";
                break;

            case 'deactivate':
                Product::whereIn('id', $ids)->update(['is_active' => false]);
                $message = "{$count} product(s) deactivated successfully.";
                break;

            case 'feature':
                Product::whereIn('id', $ids)->update(['is_featured' => true]);
                $message = "{$count} product(s) marked as featured.";
                break;

            case 'unfeature':
                Product::whereIn('id', $ids)->update(['is_featured' => false]);
                $message = "{$count} product(s) removed from featured.";
                break;

            case 'duplicate':
                $products = Product::whereIn('id', $ids)->get();
                foreach ($products as $product) {
                    $newProduct = $product->replicate();
                    $newProduct->name = $product->name . ' (Copy)';
                    $newProduct->slug = Str::slug($newProduct->name) . '-' . Str::random(5);
                    $newProduct->sku = $product->sku . '-copy-' . Str::random(5);
                    $newProduct->is_active = false;
                    $newProduct->save();
                }
                $message = "{$count} product(s) duplicated successfully.";
                break;

            default:
                return back()->with('error', 'Invalid action selected.');
        }

        return back()->with('success', $message);
    }

    /**
     * Toggle product status (AJAX).
     */
    public function toggleStatus(Request $request, Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'is_active' => $product->is_active
        ]);
    }

    /**
     * Toggle featured status (AJAX).
     */
    public function toggleFeatured(Request $request, Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);
        
        return response()->json([
            'success' => true,
            'message' => 'Featured status updated.',
            'is_featured' => $product->is_featured
        ]);
    }

    /**
     * Quick update (AJAX).
     */
    public function quickUpdate(Request $request, Product $product)
    {
        $request->validate([
            'field' => 'required|in:price,sale_price,quantity,name,category_id,product_code,short_description',
            'value' => 'required',
        ]);

        $field = $request->field;
        $value = $request->value;

        if ($field === 'price' || $field === 'sale_price') {
            $value = (float) $value;
        } elseif ($field === 'quantity') {
            $value = (int) $value;
        }

        $product->update([$field => $value]);

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'product' => $product->fresh()
        ]);
    }

    /**
     * Duplicate product.
     */
    public function duplicate(Product $product)
    {
        $newProduct = $product->replicate();
        $newProduct->name = $product->name . ' (Copy)';
        $newProduct->slug = Str::slug($newProduct->name) . '-' . Str::random(5);
        $newProduct->sku = $product->sku . '-copy-' . Str::random(5);
        $newProduct->is_active = false;

        if ($product->featured_image) {
            $oldPath = str_replace('/storage/', '', $product->featured_image);
            $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
            $newFileName = 'products/' . Str::random(40) . '.' . $extension;
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->copy($oldPath, $newFileName);
                $newProduct->featured_image = '/storage/' . $newFileName;
            }
        }

        if (!empty($product->images)) {
            $newImages = [];
            foreach ($product->images as $image) {
                $oldPath = str_replace('/storage/', '', $image);
                $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                $newFileName = 'products/' . Str::random(40) . '.' . $extension;
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->copy($oldPath, $newFileName);
                    $newImages[] = '/storage/' . $newFileName;
                }
            }
            $newProduct->images = $newImages;
        }

        $newProduct->save();

        return back()->with('success', 'Product duplicated successfully.');
    }

    /**
     * Export products to CSV.
     */
    public function export(Request $request)
    {
        $query = Product::with('category');

        // Apply same filters as index
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'SKU', 'Category', 'Price', 'Sale Price', 'Quantity', 'Status', 'Featured', 'Created At']);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->sku,
                    $product->category->name ?? 'N/A',
                    $product->price,
                    $product->sale_price ?? '',
                    $product->quantity,
                    $product->is_active ? 'Active' : 'Inactive',
                    $product->is_featured ? 'Yes' : 'No',
                    $product->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Upload additional images.
     */
    public function uploadImages(Request $request, Product $product)
    {
        $request->validate([
            'images.*' => 'required|image|max:5120',
        ]);

        $images = $product->images ?? [];

        foreach ($request->file('images') as $image) {
            // Process image with WebP conversion
            if (ImageHelper::isValidImage($image)) {
                $imageResult = ImageHelper::processImage($image, 'products/gallery', 1200, 0, 85);
                $images[] = $imageResult['path'];
            }
        }

        $product->update(['images' => json_encode($images)]);

        return back()->with('success', 'Images uploaded successfully.');
    }

    /**
     * Delete product image.
     */
    public function deleteImage(Request $request, Product $product, $image)
    {
        $images = $product->images ?? [];
        // Handle case where cast returns string (opcache or DB format issue)
        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }
        $imageIndex = (int) $image;
        
        if (isset($images[$imageIndex])) {
            ImageHelper::deleteImage($images[$imageIndex]);
            
            unset($images[$imageIndex]);
            $product->update(['images' => json_encode(array_values($images))]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Image deleted successfully.']);
        }

        return back()->with('success', 'Image deleted successfully.');
    }

    /**
     * Delete featured image.
     */
    public function deleteFeaturedImage(Request $request, Product $product)
    {
        if ($product->featured_image) {
            ImageHelper::deleteImage($product->featured_image, $product->thumbnail ?? null);
            $product->update(['featured_image' => null, 'thumbnail' => null]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Featured image deleted successfully.']);
        }

        return back()->with('success', 'Featured image deleted successfully.');
    }

    /**
     * Delete attribute image.
     */
    public function deleteAttributeImage(Request $request, Product $product, $attrId, $valueId)
    {
        $product->refresh();
        $attributes = json_decode($product->attributes ?? '{}', true);
        
        if (isset($attributes[$attrId]['values'][$valueId]['image'])) {
            ImageHelper::deleteImage($attributes[$attrId]['values'][$valueId]['image']);
            $attributes[$attrId]['values'][$valueId]['image'] = null;
            $product->update(['attributes' => json_encode($attributes)]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Attribute image deleted successfully.']);
        }

        return back()->with('success', 'Attribute image deleted successfully.');
    }

    /**
     * Delete color image.
     */
    public function deleteColorImage(Request $request, Product $product, $colorId)
    {
        $product->refresh();
        $colors = json_decode($product->colors ?? '[]', true);
        
        if ($colors) {
            foreach ($colors as $index => $color) {
                if (isset($colors[$index]['color_id']) && $colors[$index]['color_id'] == $colorId) {
                    if (isset($colors[$index]['image']) && $colors[$index]['image']) {
                        ImageHelper::deleteImage($colors[$index]['image']);
                    }
                    $colors[$index]['image'] = null;
                    $product->update(['colors' => json_encode($colors)]);
                    break;
                }
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Color image deleted successfully.']);
        }

        return back()->with('success', 'Color image deleted successfully.');
    }

    /**
     * Delete color value image.
     */
    public function deleteColorValueImage(Request $request, Product $product, $colorId, $valueId)
    {
        $product->refresh();
        $colors = json_decode($product->colors ?? '[]', true);

        if ($colors) {
            foreach ($colors as $index => $color) {
                if (isset($colors[$index]['color_id']) && $colors[$index]['color_id'] == $colorId) {
                    if (isset($colors[$index]['values'][$valueId]['image']) && $colors[$index]['values'][$valueId]['image']) {
                        ImageHelper::deleteImage($colors[$index]['values'][$valueId]['image']);
                    }
                    $colors[$index]['values'][$valueId]['image'] = null;
                    $product->update(['colors' => json_encode($colors)]);
                    break;
                }
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Color value image deleted successfully.']);
        }

        return back()->with('success', 'Color value image deleted successfully.');
    }

    /**
     * Update stock quantity for in-house product.
     */
    public function updateStock(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer',
            'operation' => 'required|in:set,add,subtract',
        ]);

        $operation = $request->operation;
        $quantity = (int) $request->quantity;

        switch ($operation) {
            case 'set':
                $product->quantity = $quantity;
                break;
            case 'add':
                $product->quantity += $quantity;
                break;
            case 'subtract':
                $product->quantity = max(0, $product->quantity - $quantity);
                break;
        }

        $product->stock_update_date = now();
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully.',
            'quantity' => $product->quantity,
            'stock_status' => $product->isOutOfStock() ? 'out_of_stock' : ($product->isLowStock() ? 'low_stock' : 'in_stock'),
        ]);
    }

    /**
     * Bulk stock update for in-house products.
     */
    public function bulkStockUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
            'operation' => 'required|in:set,add,subtract',
            'quantity' => 'required|integer|min:0',
        ]);

        $ids = $request->ids;
        $operation = $request->operation;
        $quantity = (int) $request->quantity;

        $products = Product::whereIn('id', $ids)->get();

        foreach ($products as $product) {
            switch ($operation) {
                case 'set':
                    $product->quantity = $quantity;
                    break;
                case 'add':
                    $product->quantity += $quantity;
                    break;
                case 'subtract':
                    $product->quantity = max(0, $product->quantity - $quantity);
                    break;
            }
            $product->stock_update_date = now();
            $product->save();
        }

        return response()->json([
            'success' => true,
            'message' => count($ids) . ' product(s) stock updated successfully.',
        ]);
    }

    /**
     * Update low stock threshold.
     */
    public function updateLowStockThreshold(Request $request, Product $product)
    {
        $request->validate([
            'threshold' => 'required|integer|min:0',
        ]);

        $product->low_stock_threshold = (int) $request->threshold;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Low stock threshold updated.',
            'threshold' => $product->low_stock_threshold,
        ]);
    }

    /**
     * Get low stock products for alerts.
     */
    public function lowStockAlerts(Request $request)
    {
        $products = Product::with('category')
            ->inHouse()
            ->where(function ($query) {
                $query->whereColumn('quantity', '<=', 'low_stock_threshold')
                      ->where('quantity', '>', 0);
            })
            ->orWhere('quantity', '<=', 0)
            ->orderBy('quantity', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'products' => $products,
            'count' => $products->count(),
        ]);
    }

    /**
     * Export in-house products to CSV.
     */
    public function exportInHouse(Request $request)
    {
        $query = Product::with('category')->inHouse();

        // Apply same filters as inHouse method
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        $products = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="in-house-products-' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Product Code', 'Barcode', 'Category', 'Brand', 'Purchase Price', 'Price', 'Sale Price', 'Quantity', 'Low Stock Threshold', 'Stock Value', 'Status', 'Featured', 'Created At']);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->product_code ?? $product->sku ?? '',
                    $product->barcode ?? '',
                    $product->category->name ?? 'N/A',
                    $product->brand ?? '',
                    $product->purchase_price ?? $product->cost_price ?? '',
                    $product->price,
                    $product->sale_price ?? '',
                    $product->quantity,
                    $product->low_stock_threshold,
                    $product->stock_value,
                    $product->is_active ? 'Active' : 'Inactive',
                    $product->is_featured ? 'Yes' : 'No',
                    $product->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Bulk action for in-house products only.
     */
    public function inHouseBulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
        ]);

        $action = $request->action;
        $ids = $request->ids;
        
        // Ensure only in-house products are affected
        $products = Product::whereIn('id', $ids)->inHouse()->get();
        $count = $products->count();

        if ($count === 0) {
            return back()->with('error', 'No in-house products selected.');
        }

        $productIds = $products->pluck('id')->toArray();

        switch ($action) {
            case 'delete':
                foreach ($products as $product) {
                    if ($product->featured_image) {
                        ImageHelper::deleteImage($product->featured_image, $product->thumbnail ?? null);
                    }
                    $images = $product->images ?? [];
                    foreach ($images as $image) {
                        ImageHelper::deleteImage($image);
                    }
                }
                Product::whereIn('id', $productIds)->delete();
                $message = "{$count} in-house product(s) deleted successfully.";
                break;

            case 'activate':
                Product::whereIn('id', $productIds)->update(['is_active' => true]);
                $message = "{$count} in-house product(s) activated successfully.";
                break;

            case 'deactivate':
                Product::whereIn('id', $productIds)->update(['is_active' => false]);
                $message = "{$count} in-house product(s) deactivated successfully.";
                break;

            case 'feature':
                Product::whereIn('id', $productIds)->update(['is_featured' => true]);
                $message = "{$count} in-house product(s) marked as featured.";
                break;

            case 'unfeature':
                Product::whereIn('id', $productIds)->update(['is_featured' => false]);
                $message = "{$count} in-house product(s) removed from featured.";
                break;

            case 'duplicate':
                foreach ($products as $product) {
                    $newProduct = $product->replicate();
                    $newProduct->name = $product->name . ' (Copy)';
                    $newProduct->slug = Str::slug($newProduct->name) . '-' . Str::random(5);
                    $newProduct->sku = $product->sku . '-copy-' . Str::random(5);
                    $newProduct->is_active = false;

                    if ($product->featured_image) {
                        $oldPath = str_replace('/storage/', '', $product->featured_image);
                        $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                        $newFileName = 'products/' . Str::random(40) . '.' . $extension;
                        if (Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->copy($oldPath, $newFileName);
                            $newProduct->featured_image = '/storage/' . $newFileName;
                        }
                    }

                    if (!empty($product->images)) {
                        $newImages = [];
                        foreach ($product->images as $image) {
                            $oldPath = str_replace('/storage/', '', $image);
                            $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
                            $newFileName = 'products/' . Str::random(40) . '.' . $extension;
                            if (Storage::disk('public')->exists($oldPath)) {
                                Storage::disk('public')->copy($oldPath, $newFileName);
                                $newImages[] = '/storage/' . $newFileName;
                            }
                        }
                        $newProduct->images = $newImages;
                    }

                    $newProduct->save();
                }
                $message = "{$count} in-house product(s) duplicated successfully.";
                break;

            default:
                return back()->with('error', 'Invalid action selected.');
        }

        return back()->with('success', $message);
    }

    /**
     * Display bulk import page.
     */
    public function bulkImport()
    {
        $categories = Category::where('status', 'active')->get();
        return view('admin.products.bulk-import', compact('categories'));
    }

    /**
     * Process bulk import from CSV/Excel file.
     */
    public function processBulkImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
            'default_category' => 'nullable|exists:categories,id',
            'skip_duplicates' => 'nullable|boolean',
            'update_existing' => 'nullable|boolean',
        ]);

        $file = $request->file('import_file');
        $defaultCategory = $request->default_category;
        $skipDuplicates = $request->boolean('skip_duplicates');
        $updateExisting = $request->boolean('update_existing');

        $path = $file->getRealPath();
        $extension = $file->getClientOriginalExtension();

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        try {
            if ($extension === 'csv' || $extension === 'txt') {
                $data = array_map('str_getcsv', file($path));
            } else {
                // For Excel files, use a simple approach
                $data = $this->parseExcelFile($path);
            }

            // Remove header row
            $headers = array_shift($data);

            // Normalize headers
            $headers = array_map(function ($header) {
                return strtolower(trim(str_replace([' ', '-'], '_', $header)));
            }, $headers);

            // Calculate total rows after parsing (for progress tracking)
            $totalRows = count($data);

            foreach ($data as $rowIndex => $row) {
                $rowNumber = $rowIndex + 2; // Account for header row

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Map row data to headers
                $rowData = array_combine($headers, $row);

                // Track progress
                $processed = $rowIndex + 1;
                $progress = round(($processed / $totalRows) * 100);
                
                // For very large files, you might want to flush progress periodically
                // This is a simple implementation - in production, consider using a job queue
                if ($processed % 100 === 0) {
                    // You could log progress or store it in session for AJAX updates
                    Log::info("Bulk import progress: {$progress}% - {$processed}/{$totalRows} rows processed");
                }

                // Validate required fields
                if (empty($rowData['name']) && empty($rowData['product_name'])) {
                    $errors[] = "Row {$rowNumber}: Product name is required.";
                    $skipped++;
                    continue;
                }

                // Validate numeric fields
                $price = $rowData['price'] ?? $rowData['regular_price'] ?? 0;
                $quantity = $rowData['quantity'] ?? $rowData['stock'] ?? $rowData['qty'] ?? 0;
                $lowStockThreshold = $rowData['low_stock_threshold'] ?? $rowData['min_stock'] ?? 10;

                if (!is_numeric($price)) {
                    $errors[] = "Row {$rowNumber}: Price must be a numeric value.";
                    $skipped++;
                    continue;
                }

                if (!is_numeric($quantity)) {
                    $errors[] = "Row {$rowNumber}: Quantity must be a numeric value.";
                    $skipped++;
                    continue;
                }

                if (!is_numeric($lowStockThreshold)) {
                    $errors[] = "Row {$rowNumber}: Low stock threshold must be a numeric value.";
                    $skipped++;
                    continue;
                }

                if ($quantity < 0) {
                    $errors[] = "Row {$rowNumber}: Quantity cannot be negative.";
                    $skipped++;
                    continue;
                }

                if ($lowStockThreshold < 0) {
                    $errors[] = "Row {$rowNumber}: Low stock threshold cannot be negative.";
                    $skipped++;
                    continue;
                }

                if ($quantity < $lowStockThreshold) {
                    $errors[] = "Row {$rowNumber}: Quantity must be greater than or equal to low stock threshold.";
                    $skipped++;
                    continue;
                }

                $name = $rowData['name'] ?? $rowData['product_name'] ?? null;
                $sku = $rowData['sku'] ?? $rowData['product_sku'] ?? null;

                // Check for duplicates
                $existingProduct = null;
                if ($sku) {
                    $existingProduct = Product::where('sku', $sku)->first();
                }

                if ($existingProduct && $skipDuplicates) {
                    $skipped++;
                    continue;
                }

                // Prepare product data
                $productData = [
                    'name' => $name,
                    'slug' => Str::slug($name) . '-' . Str::random(5),
                    'sku' => $sku ?: 'SKU-' . Str::random(8),
                    'product_code' => $rowData['product_code'] ?? $rowData['code'] ?? null,
                    'barcode' => $rowData['barcode'] ?? null,
                    'brand' => $rowData['brand'] ?? null,
                    'short_description' => $rowData['short_description'] ?? $rowData['summary'] ?? null,
                    'long_description' => $rowData['long_description'] ?? $rowData['description'] ?? null,
                    'price' => floatval($rowData['price'] ?? $rowData['regular_price'] ?? 0),
                    'sale_price' => !empty($rowData['sale_price']) ? floatval($rowData['sale_price']) : null,
                    'cost_price' => floatval($rowData['cost_price'] ?? $rowData['cost'] ?? 0),
                    'purchase_price' => floatval($rowData['purchase_price'] ?? 0),
                    'quantity' => intval($rowData['quantity'] ?? $rowData['stock'] ?? $rowData['qty'] ?? 0),
                    'low_stock_threshold' => intval($rowData['low_stock_threshold'] ?? $rowData['min_stock'] ?? 10),
                    'weight' => floatval($rowData['weight'] ?? 0),
                    'is_active' => isset($rowData['status']) ? strtolower($rowData['status']) === 'active' : true,
                    'is_featured' => isset($rowData['featured']) ? in_array(strtolower($rowData['featured']), ['yes', '1', 'true']) : false,
                    'meta_title' => $rowData['meta_title'] ?? null,
                    'meta_description' => $rowData['meta_description'] ?? null,
                    'meta_keywords' => $rowData['meta_keywords'] ?? null,
                    'product_source' => 'in_house',
                ];

                // Handle category
                if (!empty($rowData['category'])) {
                    $category = Category::where('name', $rowData['category'])->first();
                    if ($category) {
                        $productData['category_id'] = $category->id;
                    } elseif ($defaultCategory) {
                        $productData['category_id'] = $defaultCategory;
                    }
                } elseif ($defaultCategory) {
                    $productData['category_id'] = $defaultCategory;
                }

                // Handle tags
                if (!empty($rowData['tags'])) {
                    $productData['tags'] = array_map('trim', explode(',', $rowData['tags']));
                }

                if ($existingProduct && $updateExisting) {
                    $existingProduct->update($productData);
                    $updated++;
                } else {
                    Product::create($productData);
                    $imported++;
                }
            }

            $message = "Import completed: {$imported} products imported";
            if ($updated > 0) {
                $message .= ", {$updated} updated";
            }
            if ($skipped > 0) {
                $message .= ", {$skipped} skipped";
            }

            return back()->with('success', $message)
                        ->with('import_errors', $errors);

        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Parse Excel file using PhpSpreadsheet.
     */
    private function parseExcelFile($path)
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = [];
            
            foreach ($worksheet->getRowIterator() as $row) {
                $rowData = [];
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $data[] = $rowData;
            }
            
            return $data;
        } catch (\Exception $e) {
            throw new \Exception('Error parsing Excel file: ' . $e->getMessage());
        }
    }

    /**
     * Display bulk export page.
     */
    public function bulkExport(Request $request)
    {
        $categories = Category::where('status', 'active')->get();
        $totalProducts = Product::count();
        
        return view('admin.products.bulk-export', compact('categories', 'totalProducts'));
    }

    /**
     * Export products to CSV with full options.
     */
    public function exportProducts(Request $request)
    {
        $query = Product::with('category');

        // Apply filters
        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->stock_status) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->whereRaw('quantity > COALESCE(low_stock_threshold, 10)');
                    break;
                case 'low_stock':
                    $query->whereRaw('quantity > 0 AND quantity <= COALESCE(low_stock_threshold, 10)');
                    break;
                case 'out_of_stock':
                    $query->where('quantity', '<=', 0);
                    break;
            }
        }

        if ($request->featured !== null && $request->featured !== '') {
            $query->where('is_featured', $request->featured === 'yes');
        }

        if ($request->price_min) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->price_max) {
            $query->where('price', '<=', $request->price_max);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $products = $query->get();

        // Determine export format
        $format = $request->format ?? 'csv';
        $filename = 'products-export-' . date('Y-m-d-His');

        if ($format === 'json') {
            return response()->json($products)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '.json"');
        }

        // CSV Export
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID',
                'Name',
                'Slug',
                'SKU',
                'Product Code',
                'Barcode',
                'Category',
                'Brand',
                'Short Description',
                'Long Description',
                'Price',
                'Sale Price',
                'Cost Price',
                'Purchase Price',
                'Quantity',
                'Low Stock Threshold',
                'Weight',
                'Status',
                'Featured',
                'Product Source',
                'Meta Title',
                'Meta Description',
                'Meta Keywords',
                'Tags',
                'Created At',
                'Updated At',
            ]);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->id,
                    $product->name,
                    $product->slug,
                    $product->sku,
                    $product->product_code ?? '',
                    $product->barcode ?? '',
                    $product->category->name ?? '',
                    $product->brand ?? '',
                    $product->short_description ?? '',
                    $product->long_description ?? '',
                    $product->price,
                    $product->sale_price ?? '',
                    $product->cost_price ?? '',
                    $product->purchase_price ?? '',
                    $product->quantity,
                    $product->low_stock_threshold,
                    $product->weight ?? '',
                    $product->is_active ? 'Active' : 'Inactive',
                    $product->is_featured ? 'Yes' : 'No',
                    $product->product_source ?? 'in_house',
                    $product->meta_title ?? '',
                    $product->meta_description ?? '',
                    $product->meta_keywords ?? '',
                    is_array($product->tags) ? implode(',', $product->tags) : '',
                    $product->created_at->format('Y-m-d H:i:s'),
                    $product->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Display bulk discount page.
     */
    public function bulkDiscount()
    {
        $categories = Category::where('status', 'active')->get();
        $totalProducts = Product::count();
        
        // Get products with discounts for the discount list
        $productsWithDiscounts = Product::with('category')
            ->whereNotNull('sale_price')
            ->select(['id', 'name', 'sku', 'product_code', 'price', 'sale_price', 'category_id', 'quantity', 'discount_starts_at', 'discount_ends_at', 'is_active'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);
        
        // Calculate statistics
        $activeDiscounts = Product::whereNotNull('sale_price')
            ->where(function ($query) {
                $query->whereNull('discount_starts_at')
                    ->orWhere('discount_starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('discount_ends_at')
                    ->orWhere('discount_ends_at', '>=', now());
            })
            ->count();
        
        $scheduledDiscounts = Product::whereNotNull('sale_price')
            ->whereNotNull('discount_starts_at')
            ->where('discount_starts_at', '>', now())
            ->count();
        
        $expiredDiscounts = Product::whereNotNull('sale_price')
            ->whereNotNull('discount_ends_at')
            ->where('discount_ends_at', '<', now())
            ->count();
        
        return view('admin.products.bulk-discount', compact(
            'categories', 
            'totalProducts', 
            'productsWithDiscounts',
            'activeDiscounts',
            'scheduledDiscounts',
            'expiredDiscounts'
        ));
    }

    /**
     * Apply bulk discount to products.
     */
    public function applyBulkDiscount(Request $request)
    {
        $request->validate([
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'apply_to' => 'required|in:all,category,selected',
            'category_id' => 'required_if:apply_to,category|nullable|exists:categories,id',
            'product_ids' => 'required_if:apply_to,selected|nullable|array',
            'product_ids.*' => 'exists:products,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'clear_existing_sale_price' => 'nullable|boolean',
        ]);

        $discountType = $request->discount_type;
        $discountValue = floatval($request->discount_value);
        $applyTo = $request->apply_to;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $clearExisting = $request->boolean('clear_existing_sale_price');

        // Build query based on selection
        $query = Product::query();

        switch ($applyTo) {
            case 'category':
                $query->where('category_id', $request->category_id);
                break;
            case 'selected':
                $query->whereIn('id', $request->product_ids ?? []);
                break;
            case 'all':
            default:
                // No additional filter
                break;
        }

        // Only apply to active products
        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        // Filter by price range
        if ($request->price_min) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->price_max) {
            $query->where('price', '<=', $request->price_max);
        }

        $products = $query->get();
        $updatedCount = 0;
        $errors = [];

        foreach ($products as $product) {
            try {
                $originalPrice = $product->price;
                
                // Clear existing sale price if requested
                if ($clearExisting) {
                    $product->sale_price = null;
                }

                // Calculate new sale price
                if ($discountType === 'percentage') {
                    $discountAmount = $originalPrice * ($discountValue / 100);
                } else {
                    $discountAmount = $discountValue;
                }

                $newSalePrice = max(0, $originalPrice - $discountAmount);

                // Validate sale price is less than regular price
                if ($newSalePrice >= $originalPrice) {
                    $errors[] = "Product '{$product->name}': Sale price would be equal or greater than regular price. Skipped.";
                    continue;
                }

                $product->sale_price = round($newSalePrice, 2);
                $product->discount_starts_at = $startDate ?: null;
                $product->discount_ends_at = $endDate ?: null;
                $product->save();
                $updatedCount++;

            } catch (\Exception $e) {
                $errors[] = "Product '{$product->name}': " . $e->getMessage();
            }
        }

        $message = "Bulk discount applied to {$updatedCount} product(s).";
        
        if (!empty($errors)) {
            return back()->with('warning', $message)
                        ->with('discount_errors', $errors);
        }

        return back()->with('success', $message);
    }

    /**
     * Remove bulk discount from products.
     */
    public function removeBulkDiscount(Request $request)
    {
        $request->validate([
            'apply_to' => 'required|in:all,category,selected',
            'category_id' => 'required_if:apply_to,category|nullable|exists:categories,id',
            'product_ids' => 'required_if:apply_to,selected|nullable|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        $query = Product::query()->whereNotNull('sale_price');

        switch ($request->apply_to) {
            case 'category':
                $query->where('category_id', $request->category_id);
                break;
            case 'selected':
                $query->whereIn('id', $request->product_ids ?? []);
                break;
        }

        $count = $query->update([
            'sale_price' => null,
            'discount_starts_at' => null,
            'discount_ends_at' => null,
        ]);

        return back()->with('success', "Removed sale price from {$count} product(s).");
    }

    /**
     * Update discount for a single product (AJAX).
     */
    public function updateProductDiscount(Request $request, Product $product)
    {
        $request->validate([
            'sale_price' => 'required|numeric|min:0|lt:' . $product->price,
            'discount_starts_at' => 'nullable|date',
            'discount_ends_at' => 'nullable|date|after_or_equal:discount_starts_at',
        ]);

        $product->update([
            'sale_price' => $request->sale_price,
            'discount_starts_at' => $request->discount_starts_at ?: null,
            'discount_ends_at' => $request->discount_ends_at ?: null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Discount updated successfully.',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'discount_starts_at' => $product->discount_starts_at?->format('Y-m-d H:i:s'),
                'discount_ends_at' => $product->discount_ends_at?->format('Y-m-d H:i:s'),
                'is_on_sale' => $product->isOnSale(),
            ],
        ]);
    }

    /**
     * Remove discount from a single product (AJAX).
     */
    public function removeProductDiscount(Product $product)
    {
        $product->update([
            'sale_price' => null,
            'discount_starts_at' => null,
            'discount_ends_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Discount removed successfully.',
        ]);
    }

    /**
     * Get products for selection (AJAX).
     */
    public function getProductsForSelection(Request $request)
    {
        $query = Product::with('category')->active();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        $products = $query->select(['id', 'name', 'sku', 'price', 'sale_price', 'category_id', 'quantity', 'discount_starts_at', 'discount_ends_at'])
            ->limit(50)
            ->get();

        return response()->json([
            'products' => $products->map(function ($product) {
                $isOnSale = $product->isOnSale();
                $hasDiscount = $product->sale_price && $product->sale_price < $product->price;
                $discountPercentage = ($hasDiscount && $product->price > 0) 
                    ? round((($product->price - $product->sale_price) / $product->price) * 100) 
                    : 0;
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'discount_percentage' => $discountPercentage,
                    'category' => $product->category->name ?? 'N/A',
                    'quantity' => $product->quantity,
                    'discount_starts_at' => $product->discount_starts_at?->format('Y-m-d H:i:s'),
                    'discount_ends_at' => $product->discount_ends_at?->format('Y-m-d H:i:s'),
                    'is_on_sale' => $isOnSale,
                ];
            }),
        ]);
    }

    /**
     * Display related products management page.
     */
    public function relatedProducts(Product $product)
    {
        $product->load(['relatedProducts.category']);
        
        // Get all products except current one for selection
        $availableProducts = Product::with('category')
            ->where('id', '!=', $product->id)
            ->active()
            ->orderBy('name')
            ->get();

        return view('admin.products.related', compact('product', 'availableProducts'));
    }

    /**
     * Search products for adding as related (AJAX).
     */
    public function searchRelatedProducts(Request $request, Product $product)
    {
        $query = Product::with('category')
            ->where('id', '!=', $product->id);

        // Exclude already related products
        $relatedIds = $product->relatedProducts()->pluck('related_product_id')->toArray();
        if (!empty($relatedIds)) {
            $query->whereNotIn('id', $relatedIds);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        // Filter by status - default to active only
        $status = $request->status ?? 'active';
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $products = $query->select(['id', 'name', 'sku', 'product_code', 'price', 'sale_price', 'category_id', 'quantity', 'featured_image', 'images', 'is_active'])
            ->limit(30)
            ->get();

        return response()->json([
            'success' => true,
            'products' => $products->map(function ($item) {
                $images = is_string($item->images) ? json_decode($item->images, true) : $item->images;
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'product_code' => $item->product_code,
                    'price' => $item->price,
                    'sale_price' => $item->sale_price,
                    'final_price' => $item->final_price,
                    'category' => $item->category->name ?? 'N/A',
                    'quantity' => $item->quantity,
                    'image' => $item->featured_image ?? ($images[0] ?? null),
                    'is_active' => $item->is_active,
                ];
            }),
        ]);
    }

    /**
     * Add related products to a product.
     */
    public function addRelatedProducts(Request $request, Product $product)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id|not_in:' . $product->id,
        ]);

        $addedCount = 0;
        $existingIds = $product->relatedProducts()->pluck('related_product_id')->toArray();

        foreach ($request->product_ids as $productId) {
            // Skip if already related
            if (in_array($productId, $existingIds)) {
                continue;
            }

            // Get max sort order
            $maxOrder = DB::table('related_products')
                ->where('product_id', $product->id)
                ->max('sort_order') ?? 0;

            $product->relatedProducts()->attach($productId, [
                'sort_order' => $maxOrder + 1,
            ]);
            $addedCount++;
        }

        return response()->json([
            'success' => true,
            'message' => $addedCount > 0 
                ? "{$addedCount} related product(s) added successfully." 
                : "No new products were added (may already exist).",
            'added_count' => $addedCount,
        ]);
    }

    /**
     * Remove a related product.
     */
    public function removeRelatedProduct(Request $request, Product $product, $relatedId)
    {
        $product->relatedProducts()->detach($relatedId);

        return response()->json([
            'success' => true,
            'message' => 'Related product removed successfully.',
        ]);
    }

    /**
     * Update sort order of related products.
     */
    public function updateRelatedProductsOrder(Request $request, Product $product)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:products,id',
        ]);

        foreach ($request->order as $index => $relatedId) {
            DB::table('related_products')
                ->where('product_id', $product->id)
                ->where('related_product_id', $relatedId)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully.',
        ]);
    }

    /**
     * Bulk remove related products.
     */
    public function bulkRemoveRelatedProducts(Request $request, Product $product)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:products,id',
        ]);

        $product->relatedProducts()->detach($request->ids);

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' related product(s) removed.',
        ]);
    }

    /**
     * Auto-suggest related products based on category and tags.
     */
    public function autoSuggestRelatedProducts(Product $product)
    {
        $suggestedProducts = collect();
        
        // Get already related product IDs
        $relatedIds = $product->relatedProducts()->pluck('related_product_id')->toArray();

        // Get products from same category
        if ($product->category_id) {
            $query = Product::with('category')
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->active();
            
            if (!empty($relatedIds)) {
                $query->whereNotIn('id', $relatedIds);
            }
            
            $sameCategory = $query->limit(10)->get();
            $suggestedProducts = $suggestedProducts->merge($sameCategory);
        }

        // Get products with similar tags
        if ($product->tags && is_array($product->tags) && count($product->tags) > 0) {
            $query = Product::with('category')
                ->where(function ($q) use ($product) {
                    foreach ($product->tags as $tag) {
                        $q->orWhereJsonContains('tags', $tag);
                    }
                })
                ->where('id', '!=', $product->id)
                ->active();
            
            if (!empty($relatedIds)) {
                $query->whereNotIn('id', $relatedIds);
            }
            
            if ($suggestedProducts->count() > 0) {
                $query->whereNotIn('id', $suggestedProducts->pluck('id'));
            }
            
            $tagProducts = $query->limit(10)->get();
            $suggestedProducts = $suggestedProducts->merge($tagProducts);
        }

        // If still not enough, get featured products
        if ($suggestedProducts->count() < 5) {
            $query = Product::with('category')
                ->where('id', '!=', $product->id)
                ->active()
                ->featured();
            
            if (!empty($relatedIds)) {
                $query->whereNotIn('id', $relatedIds);
            }
            
            if ($suggestedProducts->count() > 0) {
                $query->whereNotIn('id', $suggestedProducts->pluck('id'));
            }
            
            $featured = $query->limit(10 - $suggestedProducts->count())->get();
            $suggestedProducts = $suggestedProducts->merge($featured);
        }

        return response()->json([
            'success' => true,
            'products' => $suggestedProducts->unique('id')->take(10)->map(function ($item) {
                $images = is_string($item->images) ? json_decode($item->images, true) : $item->images;
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'price' => $item->price,
                    'sale_price' => $item->sale_price,
                    'final_price' => $item->final_price,
                    'category' => $item->category->name ?? 'N/A',
                    'quantity' => $item->quantity,
                    'image' => $item->featured_image ?? ($images[0] ?? null),
                    'is_active' => $item->is_active,
                ];
            }),
        ]);
    }
    
    /**
     * Get next unique SKU by checking existing SKUs in database.
     */
    private function getNextUniqueSku()
    {
        $timestamp = date('YmdHis');
        $baseSku = 'SKU-' . $timestamp;
        
        // Check if this SKU already exists
        $exists = Product::where('sku', 'like', 'SKU-' . date('Ymd') . '%')
            ->orderBy('sku', 'desc')
            ->first();
        
        if ($exists) {
            // Extract the suffix and increment
            $parts = explode('-', $exists->sku);
            if (count($parts) >= 3) {
                $suffix = intval(end($parts));
                return 'SKU-' . date('Ymd') . '-' . ($suffix + 1);
            }
        }
        
        return $baseSku;
    }
    
    /**
     * Save variant images (combination images) for a product.
     */
    private function saveVariantImages(Product $product, Request $request)
    {
        // Get images that existed before this update (to detect deletions)
        $existingImages = $request->input('existing_variant_images', []);

        // Capture current DB keys BEFORE saving new images
        $oldDbKeys = ProductVariantImage::where('product_id', $product->id)
            ->pluck('combination_key')
            ->toArray();

        // Handle new uploaded images
        if ($request->hasFile('variant_images')) {
            foreach ($request->file('variant_images') as $combinationKey => $file) {
                $combinationKey = (string) $combinationKey;
                if ($file && $file->isValid()) {
                    // Delete existing image if any
                    ProductVariantImage::where('product_id', $product->id)
                        ->where('combination_key', $combinationKey)
                        ->delete();

                    // Process and save new image
                    $imageResult = ImageHelper::processImage($file);
                    $imagePath = $imageResult['path'] ?? null;

                    if ($imagePath) {
                        ProductVariantImage::create([
                            'product_id' => $product->id,
                            'combination_key' => $combinationKey,
                            'image' => $imagePath,
                            'sort_order' => 0,
                        ]);
                    }
                }
            }
        }

        // Remove images that were deleted via the UI (trash button)
        // Compare OLD DB keys (before new saves) with existing_variant_images keys
        $keptKeys = is_array($existingImages) ? array_keys($existingImages) : [];
        $keysToDelete = array_diff($oldDbKeys, $keptKeys);

        foreach ($keysToDelete as $key) {
            $key = (string) $key;
            $variantImage = ProductVariantImage::where('product_id', $product->id)
                ->where('combination_key', $key)
                ->first();

            if ($variantImage) {
                ImageHelper::deleteImage($variantImage->image);
                $variantImage->delete();
            }
        }
    }
}
