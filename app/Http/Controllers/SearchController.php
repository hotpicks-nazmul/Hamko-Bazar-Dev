<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\UserSearch;

class SearchController extends Controller
{
    /**
     * Search products.
     */
    public function index(Request $request)
    {
        $query = $request->get('q', '');

        $products = collect();
        $categories = collect();

        if (strlen($query) >= 2) {
            $productsQuery = Product::where('is_active', true)
                ->with('category')
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhere('sku', 'like', "%{$query}%");
                });
            
            $resultsCount = $productsQuery->count();
            $products = $productsQuery->paginate(12);

            $categories = Category::where('status', 'active')
                ->where('name', 'like', "%{$query}%")
                ->get();
            
            // Save user search
            $this->saveSearch($query, $resultsCount, false);
        }

        return view('themes.general.search.index', compact('products', 'categories', 'query'));
    }

    /**
     * Get search suggestions.
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // Search products with relevance scoring
        $products = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('short_description', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            })
            ->with('category:id,name,slug')
            ->get(['id', 'name', 'slug', 'price', 'sale_price', 'featured_image', 'category_id'])
            ->map(function ($product) use ($query) {
                // Calculate relevance score
                $product->relevance = 0;
                $lowerName = strtolower($product->name);
                $lowerQuery = strtolower($query);
                
                // Highest priority: name starts with query
                if (str_starts_with($lowerName, $lowerQuery)) {
                    $product->relevance = 100;
                }
                // High priority: name contains query at word boundary
                elseif (preg_match('/\b' . preg_quote($lowerQuery, '/') . '/i', $product->name)) {
                    $product->relevance = 80;
                }
                // Medium priority: name contains query anywhere
                elseif (str_contains($lowerName, $lowerQuery)) {
                    $product->relevance = 60;
                }
                // Lower priority: found in description or SKU
                else {
                    $product->relevance = 40;
                }
                
                return $product;
            })
            ->sortByDesc('relevance')
            ->take(8)
            ->values();

        $categories = Category::where('status', 'active')
            ->where('name', 'like', "%{$query}%")
            ->take(3)
            ->get(['id', 'name', 'slug', 'image']);

        // Save autocomplete search
        $resultsCount = $products->count() + $categories->count();
        $this->saveSearch($query, $resultsCount, true);

        return response()->json([
            'products' => $products,
            'categories' => $categories,
            'query' => $query,
        ]);
    }
    
    /**
     * Save user search to database.
     */
    private function saveSearch(string $query, int $resultsCount, bool $isAutocomplete): void
    {
        try {
            UserSearch::create([
                'query' => trim($query),
                'user_id' => auth()->check() ? auth()->id() : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'results_count' => $resultsCount,
                'is_autocomplete' => $isAutocomplete,
            ]);
        } catch (\Exception $e) {
            // Silently fail - don't break the search experience
        }
    }
}
