<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ImageHelper;
use App\Models\Color;
use App\Models\ColorValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ColorController extends Controller
{
    /**
     * Display a listing of colors.
     */
    public function index(Request $request)
    {
        $query = Color::withCount(['values', 'activeValues']);

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%")
                    ->orWhere('slug', 'like', "%{$request->search}%");
            });
        }

        // Status filter
        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        }

        // Filterable filter
        if ($request->filterable === 'yes') {
            $query->where('is_filterable', true);
        } elseif ($request->filterable === 'no') {
            $query->where('is_filterable', false);
        }

        // Sorting
        $sort = $request->sort ?? 'display_order';
        $direction = $request->direction ?? 'asc';
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->per_page ?? 25;
        $colors = $query->paginate($perPage);

        // Statistics
        $stats = [
            'total' => Color::count(),
            'active' => Color::where('is_active', true)->count(),
            'inactive' => Color::where('is_active', false)->count(),
            'filterable' => Color::where('is_filterable', true)->count(),
            'products' => \App\Models\Product::whereNotNull('colors')
                ->where('colors', '!=', '[]')
                ->count(),
        ];

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.colors.partials.table-rows', compact('colors'))->render(),
                'pagination' => $colors->links()->toHtml(),
                'stats' => $stats,
                'search' => $request->search,
            ]);
        }

        return view('admin.colors.index', compact('colors', 'stats'));
    }

    /**
     * Show the form for creating a new color.
     */
    public function create()
    {
        return view('admin.colors.create');
    }

    /**
     * Store a newly created color.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:colors,slug',
            'code' => 'nullable|string|max:10|unique:colors,code',
            'hex_code' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_filterable' => 'boolean',
            'values' => 'required|array|min:1',
            'values.*.value' => 'required|string|max:255',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'colors',
                    1920,
                    300,
                    85
                );
                $imagePath = ltrim($imageResult['path'], '/');
            }
        }

        $color = Color::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['name']),
            'code' => $validated['code'] ?? strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $validated['name']), 0, 3)),
            'hex_code' => $validated['hex_code'] ?? '#000000',
            'image' => $imagePath,
            'description' => $validated['description'] ?? null,
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => $request->has('is_active'),
            'is_filterable' => $request->has('is_filterable'),
        ]);

        if ($request->has('values')) {
            $duplicateErrors = [];
            $createdValues = [];

            foreach ($request->values as $index => $valueData) {
                if (!empty($valueData['value'])) {
                    $value = $valueData['value'];

                    // Check for duplicate within submitted values
                    $countInSubmitted = count(array_filter($createdValues, fn($v) => strtolower($v) === strtolower($value)));
                    if ($countInSubmitted > 0) {
                        $duplicateErrors["values.{$index}.value"] = "Value '{$value}' is duplicated.";
                        continue;
                    }

                    $baseSlug = Str::slug($valueData['value']);
                    $slug = $baseSlug;
                    $counter = 1;

                    // Ensure slug is unique for this color
                    while ($color->values()->where('slug', $slug)->exists()) {
                        $slug = $baseSlug . '-' . $counter;
                        $counter++;
                    }

                    ColorValue::create([
                        'color_id' => $color->id,
                        'value' => $valueData['value'],
                        'slug' => $slug,
                        'hex_code' => $valueData['hex_code'] ?? null,
                        'display_order' => $valueData['display_order'] ?? $index,
                        'is_active' => isset($valueData['is_active']) ? true : false,
                    ]);
                    $createdValues[] = $value;
                }
            }

            if (!empty($duplicateErrors)) {
                $color->delete(); // Rollback created color

                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'errors' => $duplicateErrors], 422);
                }
                return redirect()->back()->withInput()->withErrors($duplicateErrors);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Color created successfully.',
                'redirect_url' => route('admin.colors.index'),
            ]);
        }

        return redirect()->route('admin.colors.index')
            ->with('success', 'Color created successfully.');
    }

    /**
     * Show the form for editing the specified color.
     */
    public function edit(Color $color)
    {
        $color->load(['values' => function ($query) {
            $query->orderBy('display_order');
        }]);

        return view('admin.colors.edit', compact('color'));
    }

    /**
     * Update the specified color.
     */
    public function update(Request $request, Color $color)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:colors,slug,' . $color->id,
            'code' => 'nullable|string|max:10|unique:colors,code,' . $color->id,
            'hex_code' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_filterable' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($color->image) {
                ImageHelper::deleteImage($color->image);
            }
            if (ImageHelper::isValidImage($request->file('image'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('image'),
                    'colors',
                    1920,
                    300,
                    85
                );
                $validated['image'] = ltrim($imageResult['path'], '/');
            }
        }

        $color->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['name']),
            'code' => $validated['code'] ?? $color->code,
            'hex_code' => $validated['hex_code'] ?? $color->hex_code,
            'image' => $validated['image'] ?? $color->image,
            'description' => $validated['description'] ?? null,
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => $request->has('is_active'),
            'is_filterable' => $request->has('is_filterable'),
        ]);

        // Handle color values - sync existing and create new
        $duplicateErrors = [];
        $submittedIds = [];
        if ($request->has('values')) {
            foreach ($request->values as $index => $valueData) {
                if (!empty($valueData['value'])) {
                    if (!empty($valueData['id'])) {
                        $submittedIds[] = (int) $valueData['id'];
                        $colorValue = ColorValue::where('id', $valueData['id'])
                            ->where('color_id', $color->id)
                            ->first();
                        if ($colorValue) {
                            // Check for duplicate value (excluding current value)
                            $duplicateCheck = $color->values()
                                ->where('value', $valueData['value'])
                                ->where('id', '!=', $valueData['id'])
                                ->first();
                            if ($duplicateCheck) {
                                $duplicateErrors["values.{$index}.value"] = "Value '{$valueData['value']}' already exists.";
                                continue;
                            }
                            $colorValue->update([
                                'value' => $valueData['value'],
                                'hex_code' => $valueData['hex_code'] ?? null,
                                'display_order' => $valueData['display_order'] ?? $index,
                                'is_active' => isset($valueData['is_active']) ? true : false,
                            ]);
                        }
                    } else {
                        // Check if value already exists in this color
                        $existingValue = $color->values()->where('value', $valueData['value'])->first();
                        if ($existingValue) {
                            $duplicateErrors["values.{$index}.value"] = "Value '{$valueData['value']}' already exists.";
                            continue;
                        }
                        $baseSlug = Str::slug($valueData['value']);
                        $slug = $baseSlug;
                        $counter = 1;
                        while ($color->values()->where('slug', $slug)->exists()) {
                            $slug = $baseSlug . '-' . $counter;
                            $counter++;
                        }
                        $newValue = ColorValue::create([
                            'color_id' => $color->id,
                            'value' => $valueData['value'],
                            'slug' => $slug,
                            'hex_code' => $valueData['hex_code'] ?? null,
                            'display_order' => $valueData['display_order'] ?? $index,
                            'is_active' => isset($valueData['is_active']) ? true : false,
                        ]);
                        $submittedIds[] = $newValue->id;
                    }
                }
            }
        }

        // Delete values that were removed in the form
        if (!empty($submittedIds)) {
            $color->values()->whereNotIn('id', $submittedIds)->delete();
        } else {
            $color->values()->delete();
        }

        if (!empty($duplicateErrors)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $duplicateErrors], 422);
            }
            return view('admin.colors.edit', compact('color'))
                ->with('old_values', $request->input('values', []))
                ->withErrors($duplicateErrors);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Color updated successfully.',
                'color' => [
                    'values_count' => $color->values()->count(),
                    'active_values_count' => $color->activeValues()->count(),
                    'updated_at' => $color->updated_at->format('M d, Y'),
                ]
            ]);
        }

        return redirect()->route('admin.colors.index')
            ->with('success', 'Color updated successfully.');
    }

    /**
     * Remove the specified color.
     */
    public function destroy(Color $color)
    {
        // Delete image
        if ($color->image) {
            ImageHelper::deleteImage($color->image);
        }

        $color->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Color deleted successfully.',
            ]);
        }

        return redirect()->route('admin.colors.index')
            ->with('success', 'Color deleted successfully.');
    }

    /**
     * Delete color image via AJAX.
     */
    public function deleteImage(Color $color)
    {
        if ($color->image) {
            ImageHelper::deleteImage($color->image);
            $color->update(['image' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully.',
        ]);
    }

    /**
     * Toggle color status.
     */
    public function toggleStatus(Color $color)
    {
        $color->update([
            'is_active' => !$color->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Color status updated.',
            'is_active' => $color->is_active,
        ]);
    }

    /**
     * Toggle color filterable status.
     */
    public function toggleFilterable(Color $color)
    {
        $color->update([
            'is_filterable' => !$color->is_filterable,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Color filterable status updated.',
            'is_filterable' => $color->is_filterable,
        ]);
    }

    /**
     * Bulk action for colors.
     */
    public function bulkAction(Request $request)
    {
        $action = $request->action;
        $ids = json_decode($request->ids, true);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No colors selected.',
            ]);
        }

        switch ($action) {
            case 'activate':
                Color::whereIn('id', $ids)->update(['is_active' => true]);
                $message = 'Selected colors activated successfully.';
                break;
            case 'deactivate':
                Color::whereIn('id', $ids)->update(['is_active' => false]);
                $message = 'Selected colors deactivated successfully.';
                break;
            case 'delete':
                $colors = Color::whereIn('id', $ids)->get();
                foreach ($colors as $color) {
                    if ($color->image) {
                        ImageHelper::deleteImage($color->image);
                    }
                }
                Color::whereIn('id', $ids)->delete();
                $message = 'Selected colors deleted successfully.';
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid action.',
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Export colors.
     */
    public function export()
    {
        $colors = Color::withCount('products')->get();

        $filename = 'colors_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($colors) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Code', 'Hex Code', 'Status', 'Products Count', 'Display Order', 'Created At']);

            foreach ($colors as $color) {
                fputcsv($file, [
                    $color->id,
                    $color->name,
                    $color->code,
                    $color->hex_code,
                    $color->is_active ? 'Active' : 'Inactive',
                    $color->products_count,
                    $color->display_order,
                    $color->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get colors for AJAX requests.
     */
    public function getColors()
    {
        $colors = Color::active()
            ->ordered()
            ->get(['id', 'name', 'code', 'hex_code', 'image']);

        return response()->json([
            'colors' => $colors,
        ]);
    }

    /**
     * Get color values for AJAX requests.
     */
    public function getValues(Color $color)
    {
        $values = $color->values()->orderBy('display_order')->get();

        return response()->json([
            'values' => $values,
        ]);
    }

    /**
     * Get products for this color.
     */
    public function getProducts(Color $color)
    {
        $products = $color->getProductsAttribute();
        $colorId = (string) $color->id;

        $productList = $products->map(function ($product) use ($colorId) {
            $attrsData = json_decode($product->getOriginal('attributes'), true);
            $colorsData = json_decode($product->colors, true);
            $isComplete = true;
            
            // Check ALL attributes - if ANY value has price <= 0, mark Pending
            if ($attrsData && is_array($attrsData)) {
                foreach ($attrsData as $attrId => $attrData) {
                    if (isset($attrData['values']) && is_array($attrData['values'])) {
                        foreach ($attrData['values'] as $valueId => $valueData) {
                            $price = $valueData['price'] ?? null;
                            if ($price === null || $price <= 0) {
                                $isComplete = false;
                                break 2;
                            }
                        }
                    }
                }
            }
            
            // Check ALL colors - if ANY value has price <= 0, mark Pending
            if ($isComplete && $colorsData && is_array($colorsData)) {
                foreach ($colorsData as $colorItem) {
                    if (is_string($colorItem)) {
                        $colorItem = json_decode($colorItem, true);
                    }
                    
                    if ($colorItem && isset($colorItem['values']) && is_array($colorItem['values'])) {
                        foreach ($colorItem['values'] as $valueId => $valueData) {
                            if (($valueData['price'] ?? null) === null || $valueData['price'] <= 0) {
                                $isComplete = false;
                                break 2;
                            }
                        }
                    }
                }
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'status' => $product->is_active ? 'Active' : 'Inactive',
                'is_complete' => $isComplete,
            ];
        })->values()->all();

        return response()->json([
            'products' => $productList,
        ]);
    }

    /**
     * Store a new color value.
     */
    public function storeValue(Request $request, Color $color)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
            'hex_code' => 'nullable|string|max:7',
        ]);

        $createData = [
            'color_id' => $color->id,
            'value' => $validated['value'],
            'slug' => Str::slug($validated['value']),
            'hex_code' => $validated['hex_code'] ?? null,
            'display_order' => $color->values()->count(),
            'is_active' => true,
        ];

        $value = ColorValue::create($createData);

        // Sync this new value to all products using this color
        $products = $color->getProductsAttribute();
        $colorId = $color->id;
        $newValueId = (string) $value->id;

        foreach ($products as $product) {
            $colorsData = json_decode($product->colors, true);

            if (!$colorsData) {
                continue;
            }

            foreach ($colorsData as &$colorItem) {
                if (is_string($colorItem)) {
                    $colorItem = json_decode($colorItem, true);
                }

                if (isset($colorItem['color_id']) && $colorItem['color_id'] == $colorId) {
                    // Initialize values array if not exists
                    if (!isset($colorItem['values'])) {
                        $colorItem['values'] = [];
                    }

                    // Add new value if not exists
                    if (!isset($colorItem['values'][$newValueId])) {
                        $colorItem['values'][$newValueId] = [
                            'value_id' => $newValueId,
                            'value_name' => $value->value,
                            'hex_code' => $value->hex_code ?? '#000000',
                            'price' => (float) $product->price > 0 ? (float) $product->price : 1,
                            'quantity' => (int) $product->quantity > 0 ? (int) $product->quantity : 0,
                            'sku' => $product->sku ? $product->sku . '-' . Str::slug($value->value) : '',
                            'image' => null,
                            'is_visible' => true,
                        ];
                    }
                    break;
                }
            }

            $product->colors = json_encode($colorsData);
            $product->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Color value added successfully.',
            'value' => $value,
        ]);
    }

    /**
     * Update a color value.
     */
    public function updateValue(Request $request, Color $color, ColorValue $value)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
            'hex_code' => 'nullable|string|max:7',
        ]);

        $updateData = [
            'value' => $validated['value'],
            'slug' => Str::slug($validated['value']),
            'hex_code' => $validated['hex_code'] ?? null,
        ];

        $value->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Color value updated successfully.',
            'value' => $value,
        ]);
    }

    /**
     * Delete a color value.
     */
    public function destroyValue(Color $color, ColorValue $value)
    {
        $value->delete();

        return response()->json([
            'success' => true,
            'message' => 'Color value deleted successfully.',
        ]);
    }

    /**
     * Toggle color value status.
     */
    public function toggleValueStatus(Color $color, ColorValue $value)
    {
        $value->update([
            'is_active' => !$value->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Color value status updated.',
            'is_active' => $value->is_active,
        ]);
    }
}
