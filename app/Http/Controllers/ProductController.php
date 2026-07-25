<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductVariantImage;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::where('is_active', true)
            ->with('category');

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by category (include subcategories recursively)
        if ($request->has('category')) {
            $slug = $request->category;
            $category = \App\Models\Category::where('slug', $slug)->first();
            if ($category) {
                // Get all descendant category IDs recursively
                $catIds = [$category->id];
                $this->getAllChildIds($category->id, $catIds);
                $query->whereIn('category_id', $catIds);
            }
        }

        // Filter by brand
        if ($request->has('brand')) {
            $brands = $request->brand;
            if (is_array($brands) && count($brands) > 0) {
                $query->whereIn('brand_id', $brands);
            }
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by featured
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // Filter by on sale
        if ($request->boolean('on_sale')) {
            $query->whereNotNull('sale_price')
                ->whereColumn('sale_price', '<', 'price')
                ->where(function ($q) {
                    $q->whereNull('discount_ends_at')
                        ->orWhere('discount_ends_at', '>=', now());
                });
        }

        // Filter by in stock
        if ($request->boolean('in_stock')) {
            $query->where('quantity', '>', 0);
        }

        // Filter by minimum rating
        if ($request->has('rating')) {
            $rating = (int) $request->rating;
            $query->whereIn('id', function ($q) use ($rating) {
                $q->select('product_id')
                    ->from('reviews')
                    ->where('status', 'approved')
                    ->groupBy('product_id')
                    ->havingRaw('AVG(rating) >= ?', [$rating]);
            });
        }

        // Sort products
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'on_sale':
                $query->orderByRaw('CASE WHEN sale_price IS NOT NULL AND sale_price < price AND (discount_ends_at IS NULL OR discount_ends_at >= NOW()) THEN (price - sale_price) ELSE -1 END desc');
                break;
            case 'rating':
                $query->withAvg('approvedReviews', 'rating')
                    ->orderBy('approved_reviews_avg_rating', 'desc');
                break;
            case 'oldest':
                $query->oldest();
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::where('status', 'active')
            ->whereNull('parent_id')
            ->with('children.children')
            ->get();
        $brands = Brand::where('is_active', true)->orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('themes.general.products._grid', compact('products', 'categories', 'brands'))->render(),
            ]);
        }

        return view('themes.general.products.index', compact('products', 'categories', 'brands'));
    }

    /**
     * Display a single product.
     */
    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['category', 'reviews.user', 'attributeValues.attribute', 'colors', 'relatedProducts', 'variants'])
            ->firstOrFail();

        // If this is a variant, redirect to parent or get parent info
        if ($product->parent_id) {
            $parentProduct = Product::find($product->parent_id);
            if ($parentProduct && $parentProduct->slug !== $slug) {
                return redirect()->route('product.show', $parentProduct->slug);
            }
        }

        // Get manual variants (children products)
        $variants = $product->variants()->where('is_active', true)->get();
        
        // Build variant options for frontend with price and image
        $variantOptions = [];
        $variantImages = [];
        
        foreach ($variants as $variant) {
            $variations = json_decode($variant->variations, true);
            if (!empty($variations) && is_array($variations)) {
                foreach ($variations as $var) {
                    $attrName = $var['attrName'] ?? '';
                    $valueName = $var['valueName'] ?? '';
                    
                    if (!isset($variantOptions[$attrName])) {
                        $variantOptions[$attrName] = [];
                    }
                    
                    if (!isset($variantOptions[$attrName][$valueName])) {
                        $variantOptions[$attrName][$valueName] = [
                            'value' => $valueName,
                            'variants' => []
                        ];
                    }
                    
                    $variantOptions[$attrName][$valueName]['variants'][] = [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'sale_price' => $variant->sale_price,
                        'stock' => $variant->quantity,
                        'image' => $variant->featured_image
                    ];
                    
                    // Store variant image for main product image swap
                    $variantImages[$variant->id] = $variant->featured_image;
                }
            }
        }

        // Build attribute options from JSON column
        $attributeOptions = [];
        $productAttrsRaw = $product->attributes;
        // Handle both string (JSON) and array
        $productAttrs = is_string($productAttrsRaw) ? json_decode($productAttrsRaw, true) : ($productAttrsRaw ?? []);
        
        if (!empty($productAttrs) && is_array($productAttrs)) {
            // Get all attributes for reference
            $allAttributes = \App\Models\Attribute::all()->keyBy('id');
            
            foreach ($productAttrs as $attrId => $attrData) {
                // Use saved name, or fallback to attribute name from DB
                $attrName = $attrData['name'] ?? '';
                if (empty($attrName) && isset($allAttributes[$attrId])) {
                    $attrName = $allAttributes[$attrId]->name;
                }
                if (empty($attrName)) {
                    $attrName = 'Option ' . $attrId;
                }
                
                if (isset($attrData['values']) && is_array($attrData['values'])) {
                    foreach ($attrData['values'] as $valueId => $value) {
                        if (!isset($attributeOptions[$attrName])) {
                            $attributeOptions[$attrName] = [];
                        }
                        $exists = in_array($value['value_name'] ?? '', array_column($attributeOptions[$attrName], 'value'));
                        if (!$exists && !empty($value['value_name'])) {
                            $attributeOptions[$attrName][] = [
                                'id' => $valueId,
                                'value' => $value['value_name'] ?? '',
                                'price' => $value['price'] ?? 0,
                                'color_code' => $value['color_code'] ?? null,
                                'image' => isset($value['image']) ? preg_replace('/^\/storage\//', '', $value['image']) : null,
                                'quantity' => $value['quantity'] ?? 0,
                                'sku' => $value['sku'] ?? null,
                            ];
                        }
                    }
                }
            }
        }

        // Get manually configured related products first
        $relatedProducts = $product->relatedProducts()
            ->where('is_active', true)
            ->with('category')
            ->limit(8)
            ->get();

        // If not enough related products, supplement with products from same category
        if ($relatedProducts->count() < 4) {
            $categoryProducts = Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->where('is_active', true)
                ->with('category')
                ->whereNotIn('id', $relatedProducts->pluck('id'))
                ->limit(8 - $relatedProducts->count())
                ->get();
            
            $relatedProducts = $relatedProducts->merge($categoryProducts);
        }

        // Get approved reviews with pagination
        $reviews = $product->approvedReviews()->latest()->paginate(5);

        // Get product attributes grouped by attribute name (from JSON column - fallback)
        $attributes = [];

        // Get colors from JSON column (admin saves here, not in pivot)
        $colors = collect([]);
        $colorOptions = [];
        $productColorsRaw = $product->colors;
        $productColors = is_string($productColorsRaw) ? json_decode($productColorsRaw, true) : ($productColorsRaw ?? []);
        
        $defaultHexCodes = [
            'red' => '#FF0000', 'green' => '#00FF00', 'blue' => '#0000FF',
            'yellow' => '#FFFF00', 'orange' => '#FFA500', 'purple' => '#800080',
            'pink' => '#FFC0CB', 'brown' => '#A52A2A', 'black' => '#000000',
            'white' => '#FFFFFF', 'gray' => '#808080', 'grey' => '#808080',
            'navy' => '#000080', 'teal' => '#008080', 'olive' => '#808000',
            'maroon' => '#800000', 'aqua' => '#00FFFF', 'silver' => '#C0C0C0',
            'beige' => '#F5F5DC', 'cream' => '#FFFDD0', 'gold' => '#FFD700',
            'cyan' => '#00FFFF', 'magenta' => '#FF00FF', 'lime' => '#00FF00',
            'coral' => '#FF7F50', 'salmon' => '#FA8072', 'khaki' => '#F0E68C',
            'indigo' => '#4B0082', 'violet' => '#EE82EE', 'orange-red' => '#FF4500',
            'pink' => '#FF69B4', 'pinki' => '#FF69B4', 'yellowish' => '#FFD700',
        ];
        
        if (!empty($productColors) && is_array($productColors)) {
            foreach ($productColors as $colorItem) {
                // Handle nested 'values' structure from admin panel
                if (isset($colorItem['values']) && is_array($colorItem['values'])) {
                    foreach ($colorItem['values'] as $valueId => $valueData) {
                        $hexCode = $valueData['hex_code'] ?? null;
                        if (!$hexCode || $hexCode === '#000000') {
                            $colorName = strtolower($valueData['value_name'] ?? '');
                            $hexCode = $defaultHexCodes[$colorName] ?? $this->generateHexFromName($valueData['value_name'] ?? '');
                        }
                        $colorOptions[] = [
                            'id' => $valueId,
                            'name' => $valueData['value_name'] ?? '',
                            'hex_code' => $hexCode,
                            'image' => isset($valueData['image']) ? preg_replace('/^\/storage\//', '', $valueData['image']) : null,
                            'price' => $valueData['price'] ?? 0,
                            'quantity' => $valueData['quantity'] ?? 0,
                            'sku' => $valueData['sku'] ?? null,
                        ];
                    }
                }
                // Handle flat structure (direct color_id)
                elseif (isset($colorItem['color_id'])) {
                    $colorId = $colorItem['color_id'];
                    $colorModel = \App\Models\Color::find($colorId);
                    if ($colorModel) {
                        $colors->push($colorModel);
                        $colorOptions[] = [
                            'id' => $colorModel->id,
                            'name' => $colorModel->name,
                            'hex_code' => $colorModel->hex_code,
                            'image' => $colorItem['image'] ?? null,
                            'price' => $colorItem['price'] ?? 0,
                            'quantity' => $colorItem['quantity'] ?? 0,
                            'sku' => $colorItem['sku'] ?? null,
                        ];
                    }
                }
            }
        }

        // Get all variant images for gallery
        $allVariantImages = \App\Models\ProductVariantImage::where('product_id', $product->id)
            ->orderBy('sort_order')
            ->get();

        return view('themes.general.products.show', compact('product', 'relatedProducts', 'reviews', 'attributes', 'colors', 'colorOptions', 'variants', 'variantOptions', 'attributeOptions', 'variantImages', 'allVariantImages'));
    }
    
    private function generateHexFromName($name)
    {
        if (empty($name)) return '#000000';
        $hash = md5(strtolower($name));
        return '#' . substr($hash, 0, 6);
    }

    /**
     * Display products by category.
     */
    public function byCategory($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        
        // Get all descendant category IDs recursively
        $catIds = [$category->id];
        $this->getAllChildIds($category->id, $catIds);
        
        $products = Product::whereIn('category_id', $catIds)
            ->where('is_active', true)
            ->with('category')
            ->paginate(12);

        return view('themes.general.products.category', compact('products', 'category'));
    }

    /**
     * Quick view for product.
     */
    public function quickView($id)
    {
        $product = Product::with([
            'category',
            'approvedReviews' => function ($q) {
                $q->select('id', 'product_id', 'rating');
            },
        ])->withCount('approvedReviews')->findOrFail($id);
        
        return view('themes.general.partials.quick-view-content', compact('product'));
    }

    /**
     * Get variant image for a combination key.
     */
    public function getVariantImage(Request $request, $productId)
    {
        $key = $request->query('key', '');
        
        if (empty($key)) {
            return response()->json(['image' => null]);
        }
        
        $variantImage = ProductVariantImage::where('product_id', $productId)
            ->where('combination_key', $key)
            ->first();
        
        if ($variantImage && $variantImage->image) {
            return response()->json(['image' => asset($variantImage->image)]);
        }
        
        return response()->json(['image' => null]);
    }

    /**
     * Recursively get all child category IDs.
     */
    private function getAllChildIds($parentId, &$ids)
    {
        $children = Category::where('parent_id', $parentId)->pluck('id')->toArray();
        foreach ($children as $childId) {
            $ids[] = $childId;
            $this->getAllChildIds($childId, $ids);
        }
    }
}
