<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariantImage;
use App\Models\Category;
use App\Services\ThemeService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $theme;

    public function __construct(ThemeService $theme)
    {
        $this->theme = $theme;
    }

    /**
     * Display products listing.
     */
    public function index(Request $request)
    {
        $query = Product::active()
            ->with('category')
            ->inStock();

        // Filter by category
        if ($request->category) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Filter by price range
        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('short_description', 'like', "%{$request->search}%");
            });
        }

        // Sort products
        $sortBy = $request->sort ?? 'latest';
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
            default:
                $query->latest();
        }

        $products = $query->paginate(12);
        $categories = Category::active()->parents()->ordered()->get();

        if ($request->ajax() || $request->wantsJson()) {
            $html = view('themes.general.products._grid', compact('products', 'categories'))->render();
            return response()->json(['html' => $html]);
        }

        return $this->theme->view('products.index', compact('products', 'categories'));
    }

    /**
     * Display single product.
     */
    public function show($slug)
    {
        $product = Product::active()
            ->with(['category', 'reviews.user'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Get related products
        $relatedProducts = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with('category')
            ->take(4)
            ->get();

        // If less than 4, get from parent categories
        if ($relatedProducts->count() < 4 && $product->category) {
            $categoryIds = collect([$product->category_id]);
            if ($product->category->parent) {
                $categoryIds->push($product->category->parent_id);
                if ($product->category->parent->parent) {
                    $categoryIds->push($product->category->parent->parent_id);
                }
            }
            $categoryProducts = Product::active()
                ->whereIn('category_id', $categoryIds)
                ->where('id', '!=', $product->id)
                ->with('category')
                ->whereNotIn('id', $relatedProducts->pluck('id'))
                ->take(4 - $relatedProducts->count())
                ->get();
            $relatedProducts = $relatedProducts->merge($categoryProducts);
        }

        // Get approved reviews
        $reviews = $product->approvedReviews()->latest()->paginate(5);

        // Process colors from JSON column
        $colors = collect([]);
        $colorOptions = [];
        $productColorsRaw = $product->colors;
        $productColors = is_string($productColorsRaw) ? json_decode($productColorsRaw, true) : ($productColorsRaw ?? []);
        
        // Default hex codes mapped by common color names
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
                } elseif (isset($colorItem['color_id'])) {
                    $colorId = $colorItem['color_id'];
                    $colorModel = \App\Models\Color::find($colorId);
                    if ($colorModel) {
                        $colors->push($colorModel);
                        $colorOptions[] = [
                            'id' => $colorModel->id,
                            'name' => $colorModel->name,
                            'hex_code' => $colorModel->hex_code ?? '#000000',
                            'image' => $colorItem['image'] ?? null,
                            'price' => $colorItem['price'] ?? 0,
                            'quantity' => $colorItem['quantity'] ?? 0,
                            'sku' => $colorItem['sku'] ?? null,
                        ];
                    }
                }
            }
        }

        return $this->theme->view('products.show', compact('product', 'relatedProducts', 'reviews', 'colors', 'colorOptions'));
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
    public function category($slug)
    {
        $category = Category::active()
            ->where('slug', $slug)
            ->firstOrFail();

        $products = Product::active()
            ->where('category_id', $category->id)
            ->with('category')
            ->inStock()
            ->paginate(12);

        return $this->theme->view('products.category', compact('category', 'products'));
    }

    /**
     * Get featured products (API).
     */
    public function featured()
    {
        $products = Product::active()
            ->featured()
            ->inStock()
            ->take(8)
            ->get();

        return response()->json($products);
    }

    /**
     * Get related products (API).
     */
    public function related($productId)
    {
        $product = Product::findOrFail($productId);
        
        $products = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inStock()
            ->take(4)
            ->get();

        return response()->json($products);
    }
    
    /**
     * Get variant image for a specific combination (API).
     */
    public function getVariantImage($productId, Request $request)
    {
        $combinationKey = $request->input('key');
        
        if (!$combinationKey) {
            return response()->json(['image' => null]);
        }
        
        $variantImage = ProductVariantImage::where('product_id', $productId)
            ->where('combination_key', $combinationKey)
            ->first();
        
        return response()->json([
            'image' => $variantImage ? asset('storage/' . $variantImage->image) : null,
        ]);
    }
}
