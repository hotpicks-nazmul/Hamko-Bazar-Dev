<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display all categories (unlimited depth).
     */
    public function index()
    {
        $allCategories = Category::where('status', 'active')
            ->withCount('products')
            ->orderBy('name')
            ->get();
        $grouped = $allCategories->groupBy('parent_id');
        $buildTree = function($parentId) use ($grouped, &$buildTree) {
            $children = $grouped->get($parentId, collect());
            return $children->map(function($cat) use ($buildTree) {
                $cat->setRelation('children', $buildTree($cat->id));
                return $cat;
            });
        };
        $categories = $buildTree(null);

        return view('themes.general.categories.index', compact('categories'));
    }

    /**
     * Display a single category.
     */
    public function show($slug)
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $products = $category->products()
            ->where('is_active', true)
            ->paginate(12);

        return view('themes.general.categories.show', compact('category', 'products'));
    }
}
