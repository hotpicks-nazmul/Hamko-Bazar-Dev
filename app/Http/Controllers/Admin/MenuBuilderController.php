<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MenuBuilderController extends Controller
{
    /**
     * Display a listing of the menus.
     */
    public function index()
    {
        $menus = Menu::latest()->paginate(15);
        return view('admin.content.menus.index', compact('menus'));
    }

    /**
     * Show the form for creating a new menu.
     */
    public function create()
    {
        return view('admin.content.menus.create');
    }

    /**
     * Store a newly created menu in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:menus,name',
            'slug' => 'nullable|string|max:255|unique:menus,slug',
            'location' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($request->name);
        }

        // Ensure slug is unique
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Menu::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        Menu::create($data);

        return redirect()->route('admin.menus.index')
            ->with('success', 'Menu created successfully.');
    }

    /**
     * Show the form for editing the specified menu.
     */
    public function edit($id)
    {
        $menu = Menu::findOrFail($id);
        return view('admin.content.menus.edit', compact('menu'));
    }

    /**
     * Update the specified menu in storage.
     */
    public function update(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:menus,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:menus,slug,' . $id,
            'location' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();

        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($request->name);
        }

        // Ensure slug is unique
        $originalSlug = $data['slug'];
        $counter = 1;
        while (Menu::where('slug', $data['slug'])->where('id', '!=', $id)->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        $menu->update($data);

        return redirect()->route('admin.menus.index')
            ->with('success', 'Menu updated successfully.');
    }

    /**
     * Remove the specified menu from storage.
     */
    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return redirect()->route('admin.menus.index')
            ->with('success', 'Menu deleted successfully.');
    }

    /**
     * Toggle the status of the menu.
     */
    public function toggleStatus($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->toggleStatus();

        return back()->with('success', 'Menu status updated.');
    }

    /**
     * Show the form for managing menu items.
     */
    public function items($id)
    {
        $menu = Menu::with('items')->findOrFail($id);
        
        // Get categories for linking
        $categories = Category::where('parent_id', 0)
            ->with('children')
            ->active()
            ->orderBy('name')
            ->get();
        
        // Get pages for linking
        $pages = Page::orderBy('title')->get();
        
        // Get products for linking
        $products = Product::where('published', 1)
            ->orderBy('name')
            ->select('id', 'name', 'slug')
            ->limit(50)
            ->get();

        // Organize menu items into a tree structure
        $rootItems = $menu->items()->whereNull('parent_id')->ordered()->with('children')->get();

        return view('admin.content.menus.items', compact('menu', 'categories', 'pages', 'products', 'rootItems'));
    }

    /**
     * Store a new menu item.
     */
    public function storeItem(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:custom,category,page,product,link',
            'url' => 'nullable|string|max:500',
            'target' => 'nullable|in:_self,_blank,_parent,_top',
            'icon' => 'nullable|string|max:100',
            'css_class' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:menu_items,id',
            'reference_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();

        // Process URL based on type
        if ($request->type === 'category' && $request->reference_id) {
            $category = Category::find($request->reference_id);
            if ($category) {
                $data['url'] = 'category/' . $category->slug;
            }
        } elseif ($request->type === 'page' && $request->reference_id) {
            $page = Page::find($request->reference_id);
            if ($page) {
                $data['url'] = 'page/' . $page->slug;
            }
        } elseif ($request->type === 'product' && $request->reference_id) {
            $product = Product::find($request->reference_id);
            if ($product) {
                $data['url'] = 'product/' . $product->slug;
            }
        } elseif ($request->type === 'link') {
            // For external links, ensure URL has proper format
            if (!empty($data['url']) && !str_starts_with($data['url'], 'http')) {
                $data['url'] = 'https://' . ltrim($data['url'], '/');
            }
        }

        // Get max sort order if not provided
        if (!isset($data['sort_order'])) {
            $maxOrder = $menu->items()->max('sort_order') ?? 0;
            $data['sort_order'] = $maxOrder + 1;
        }

        $menu->items()->create($data);

        return back()->with('success', 'Menu item added successfully.');
    }

    /**
     * Update a menu item.
     */
    public function updateItem(Request $request, $id, $itemId)
    {
        $menu = Menu::findOrFail($id);
        $menuItem = MenuItem::where('menu_id', $id)->findOrFail($itemId);

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:custom,category,page,product,link',
            'url' => 'nullable|string|max:500',
            'target' => 'nullable|in:_self,_blank,_parent,_top',
            'icon' => 'nullable|string|max:100',
            'css_class' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:menu_items,id',
            'reference_id' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();

        // Prevent setting itself as parent
        if ($data['parent_id'] == $itemId) {
            $data['parent_id'] = null;
        }

        // Process URL based on type
        if ($request->type === 'category' && $request->reference_id) {
            $category = Category::find($request->reference_id);
            if ($category) {
                $data['url'] = 'category/' . $category->slug;
            }
        } elseif ($request->type === 'page' && $request->reference_id) {
            $page = Page::find($request->reference_id);
            if ($page) {
                $data['url'] = 'page/' . $page->slug;
            }
        } elseif ($request->type === 'product' && $request->reference_id) {
            $product = Product::find($request->reference_id);
            if ($product) {
                $data['url'] = 'product/' . $product->slug;
            }
        } elseif ($request->type === 'link') {
            // For external links, ensure URL has proper format
            if (!empty($data['url']) && !str_starts_with($data['url'], 'http')) {
                $data['url'] = 'https://' . ltrim($data['url'], '/');
            }
        }

        $menuItem->update($data);

        return back()->with('success', 'Menu item updated successfully.');
    }

    /**
     * Delete a menu item.
     */
    public function destroyItem($id, $itemId)
    {
        $menu = Menu::findOrFail($id);
        $menuItem = MenuItem::where('menu_id', $id)->findOrFail($itemId);

        // If item has children, move them to parent
        if ($menuItem->children()->count() > 0) {
            $parentId = $menuItem->parent_id;
            $menuItem->children()->update(['parent_id' => $parentId]);
        }

        $menuItem->delete();

        return back()->with('success', 'Menu item deleted successfully.');
    }

    /**
     * Toggle menu item status.
     */
    public function toggleItemStatus($id, $itemId)
    {
        $menu = Menu::findOrFail($id);
        $menuItem = MenuItem::where('menu_id', $id)->findOrFail($itemId);
        $menuItem->toggleStatus();

        return back()->with('success', 'Menu item status updated.');
    }

    /**
     * Reorder menu items.
     */
    public function reorderItems(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.parent_id' => 'nullable',
            'items.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->items as $item) {
            MenuItem::where('menu_id', $id)
                ->where('id', $item['id'])
                ->update([
                    'parent_id' => $item['parent_id'] ?? null,
                    'sort_order' => $item['sort_order']
                ]);
        }

        return response()->json(['success' => true, 'message' => 'Menu items reordered successfully.']);
    }

    /**
     * Get available link options for menu item.
     */
    public function getLinkOptions(Request $request)
    {
        $type = $request->type;
        $options = [];

        switch ($type) {
            case 'category':
                $categories = Category::where('parent_id', 0)
                    ->with('children')
                    ->active()
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug']);
                $options = $categories->map(function($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => $cat->name,
                        'slug' => $cat->slug,
                        'children' => $cat->children->map(function($child) {
                            return [
                                'id' => $child->id,
                                'name' => '-- ' . $child->name,
                                'slug' => $child->slug
                            ];
                        })
                    ];
                });
                break;

            case 'page':
                $pages = Page::orderBy('title')->get(['id', 'title', 'slug']);
                $options = $pages->map(function($page) {
                    return [
                        'id' => $page->id,
                        'name' => $page->title,
                        'slug' => $page->slug
                    ];
                });
                break;

            case 'product':
                $products = Product::where('published', 1)
                    ->orderBy('name')
                    ->select('id', 'name', 'slug')
                    ->limit(50)
                    ->get();
                $options = $products->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug
                    ];
                });
                break;
        }

        return response()->json($options);
    }
}
