<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    /**
     * Display a listing of attributes.
     */
    public function index(Request $request)
    {
        $query = Attribute::withCount(['values', 'values as active_values_count' => function ($q) {
            $q->where('is_active', true);
        }]);

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
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
        $attributes = $query->paginate($perPage);

        // Statistics
        $stats = [
            'total' => Attribute::count(),
            'active' => Attribute::where('is_active', true)->count(),
            'inactive' => Attribute::where('is_active', false)->count(),
            'filterable' => Attribute::where('is_filterable', true)->count(),
        ];

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.attributes.partials.table-rows', compact('attributes'))->render(),
                'pagination' => $attributes->links()->toHtml(),
                'stats' => $stats,
            ]);
        }

        return view('admin.attributes.index', compact('attributes', 'stats'));
    }

    /**
     * Show the form for creating a new attribute.
     */
    public function create()
    {
        return view('admin.attributes.create');
    }

    /**
     * Store a newly created attribute.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:attributes,slug',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
            'is_filterable' => 'nullable',
            'values' => 'nullable|array',
            'values.*.value' => 'sometimes|nullable|string|max:255',
            'values.*.price' => 'nullable|numeric|min:0',
            'values.*.image' => 'nullable|image|max:2048',
        ], [
            'values.*.value.unique' => 'This value already exists.',
        ]);

        // Create attribute
        $attribute = Attribute::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['name']),
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
                    while (AttributeValue::where('slug', $slug)->exists()) {
                        $slug = $baseSlug . '-' . $counter;
                        $counter++;
                    }
                    
                    $newValue = AttributeValue::create([
                        'attribute_id' => $attribute->id,
                        'value' => $value,
                        'slug' => $slug,
                        'display_order' => $valueData['display_order'] ?? $index,
                        'is_active' => isset($valueData['is_active']) ? true : false,
                    ]);
                    $createdValues[] = $value;
                }
            }
            
            if (!empty($duplicateErrors)) {
                $attribute->delete(); // Rollback created attribute
                
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'errors' => $duplicateErrors], 422);
                }
                return redirect()->back()->withInput()->withErrors($duplicateErrors);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Attribute created successfully.',
                'redirect_url' => route('admin.attributes.index'),
            ]);
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute created successfully.');
    }

    /**
     * Show the form for editing the specified attribute.
     */
    public function edit(Attribute $attribute)
    {
        $attribute->load(['values' => function ($query) {
            $query->orderBy('display_order');
        }]);
        $attribute->loadCount(['values', 'values as active_values_count' => function ($q) {
            $q->where('is_active', true);
        }]);
        return view('admin.attributes.edit', compact('attribute'));
    }

    /**
     * Update the specified attribute.
     */
    public function update(Request $request, Attribute $attribute)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:attributes,slug,' . $attribute->id,
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
            'is_filterable' => 'nullable',
        ]);

        $attribute->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => $request->has('is_active'),
            'is_filterable' => $request->has('is_filterable'),
        ]);

        // Handle attribute values - sync existing and create new
        $duplicateErrors = [];
        $submittedIds = [];
        if ($request->has('values')) {
            foreach ($request->values as $index => $valueData) {
                if (!empty($valueData['value'])) {
                    if (!empty($valueData['id'])) {
                        $submittedIds[] = (int) $valueData['id'];
                        $attributeValue = AttributeValue::where('id', $valueData['id'])
                            ->where('attribute_id', $attribute->id)
                            ->first();
                        if ($attributeValue) {
                            $duplicateCheck = $attribute->values()
                                ->where('value', $valueData['value'])
                                ->where('id', '!=', $valueData['id'])
                                ->first();
                            if ($duplicateCheck) {
                                $duplicateErrors["values.{$index}.value"] = "Value '{$valueData['value']}' already exists.";
                                continue;
                            }
                            $attributeValue->update([
                                'value' => $valueData['value'],
                                'display_order' => $valueData['display_order'] ?? $index,
                                'is_active' => isset($valueData['is_active']) ? true : false,
                            ]);
                        }
                    } else {
                        $existingValue = $attribute->values()->where('value', $valueData['value'])->first();
                        if ($existingValue) {
                            $duplicateErrors["values.{$index}.value"] = "Value '{$valueData['value']}' already exists.";
                            continue;
                        }
                        $baseSlug = Str::slug($valueData['value']);
                        $slug = $baseSlug;
                        $counter = 1;
                        while ($attribute->values()->where('slug', $slug)->exists()) {
                            $slug = $baseSlug . '-' . $counter;
                            $counter++;
                        }
                        $newValue = AttributeValue::create([
                            'attribute_id' => $attribute->id,
                            'value' => $valueData['value'],
                            'slug' => $slug,
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
            $attribute->values()->whereNotIn('id', $submittedIds)->delete();
        } else {
            $attribute->values()->delete();
        }

        $attribute->load(['values' => function ($query) {
            $query->orderBy('display_order');
        }]);
        $attribute->loadCount(['values', 'values as active_values_count' => function ($q) {
            $q->where('is_active', true);
        }]);

        if (!empty($duplicateErrors)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $duplicateErrors], 422);
            }
            return view('admin.attributes.edit', compact('attribute'))
                ->with('old_values', $request->input('values', []))
                ->withErrors($duplicateErrors);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Attribute updated successfully.',
                'attribute' => [
                    'values_count' => $attribute->values_count,
                    'active_values_count' => $attribute->active_values_count,
                    'updated_at' => $attribute->updated_at->format('M d, Y'),
                ]
            ]);
        }

        return redirect()->route('admin.attributes.edit', $attribute->id)
            ->with('success', 'Attribute updated successfully.');
    }

    /**
     * Remove the specified attribute.
     */
    public function destroy(Attribute $attribute)
    {
        $attribute->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Attribute deleted successfully.',
            ]);
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribute deleted successfully.');
    }

    /**
     * Toggle attribute status.
     */
    public function toggleStatus(Attribute $attribute)
    {
        $attribute->update([
            'is_active' => !$attribute->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attribute status updated.',
            'is_active' => $attribute->is_active,
        ]);
    }

    /**
     * Toggle attribute filterable status.
     */
    public function toggleFilterable(Attribute $attribute)
    {
        $attribute->update([
            'is_filterable' => !$attribute->is_filterable,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attribute filterable status updated.',
            'is_filterable' => $attribute->is_filterable,
        ]);
    }

    /**
     * Bulk action for attributes.
     */
    public function bulkAction(Request $request)
    {
        $action = $request->action;
        $ids = json_decode($request->ids, true);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No attributes selected.',
            ]);
        }

        switch ($action) {
            case 'activate':
                Attribute::whereIn('id', $ids)->update(['is_active' => true]);
                $message = 'Selected attributes activated successfully.';
                break;
            case 'deactivate':
                Attribute::whereIn('id', $ids)->update(['is_active' => false]);
                $message = 'Selected attributes deactivated successfully.';
                break;
            case 'delete':
                Attribute::whereIn('id', $ids)->delete();
                $message = 'Selected attributes deleted successfully.';
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
     * Export attributes.
     */
    public function export()
    {
        $attributes = Attribute::withCount('products')->with('values')->get();

        $filename = 'attributes_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($attributes) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Slug', 'Description', 'Status', 'Filterable', 'Values Count', 'Products Count', 'Created At']);

            foreach ($attributes as $attribute) {
                fputcsv($file, [
                    $attribute->id,
                    $attribute->name,
                    $attribute->slug,
                    $attribute->description,
                    $attribute->is_active ? 'Active' : 'Inactive',
                    $attribute->is_filterable ? 'Yes' : 'No',
                    $attribute->values_count,
                    $attribute->products_count,
                    $attribute->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ==================== Attribute Value Methods ====================

    /**
     * Store a new attribute value.
     */
    public function storeValue(Request $request, Attribute $attribute)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
        ]);

        $createData = [
            'attribute_id' => $attribute->id,
            'value' => $validated['value'],
            'slug' => Str::slug($validated['value']),
            'display_order' => $attribute->values()->count(),
            'is_active' => true,
        ];

        $value = AttributeValue::create($createData);

        // Sync this new value to all products using this attribute
        $products = $attribute->getProductsAttribute();
        $attrId = (string) $attribute->id;
        $newValueId = (string) $value->id;

        foreach ($products as $product) {
            $attrsData = json_decode($product->attributes, true);

            if (!$attrsData) {
                $attrsData = [];
            }

            // Initialize attribute if not exists
            if (!isset($attrsData[$attrId])) {
                $attrsData[$attrId] = ['name' => '', 'values' => []];
            }

            // Add new value if not exists
            if (!isset($attrsData[$attrId]['values'][$newValueId])) {
                $attrsData[$attrId]['values'][$newValueId] = [
                    'value_id' => $newValueId,
                    'value_name' => $value->value,
                    'price' => (float) $product->price > 0 ? (float) $product->price : 1,
                    'quantity' => (int) $product->quantity > 0 ? (int) $product->quantity : 0,
                    'sku' => $product->sku ? $product->sku . '-' . Str::slug($value->value) : '',
                    'image' => null,
                    'is_visible' => true,
                ];

                $product->attributes = json_encode($attrsData);
                $product->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Attribute value added successfully.',
            'value' => $value,
        ]);
    }

    /**
     * Update an attribute value.
     */
    public function updateValue(Request $request, Attribute $attribute, AttributeValue $value)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
        ]);

        $updateData = [
            'value' => $validated['value'],
            'slug' => Str::slug($validated['value']),
        ];

        $value->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Attribute value updated successfully.',
            'value' => $value,
        ]);
    }

    /**
     * Delete an attribute value.
     */
    public function destroyValue(Attribute $attribute, AttributeValue $value)
    {
        $value->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attribute value deleted successfully.',
        ]);
    }

    /**
     * Toggle attribute value status.
     */
    public function toggleValueStatus(Attribute $attribute, AttributeValue $value)
    {
        $value->update([
            'is_active' => !$value->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attribute value status updated.',
            'is_active' => $value->is_active,
        ]);
    }

    /**
     * Get attribute values for AJAX requests.
     */
    public function getValues(Attribute $attribute)
    {
        return response()->json([
            'values' => $attribute->values()->orderBy('display_order')->get(),
        ]);
    }

/**
     * Get products for this attribute.
     */
    public function getProducts(Attribute $attribute)
    {
        $products = $attribute->getProductsAttribute();
        $attrId = (string) $attribute->id;

        $productList = $products->map(function ($product) use ($attrId) {
            $attrsData = json_decode($product->getOriginal('attributes'), true);
            $colorsData = json_decode($product->colors, true);
            $isComplete = true;
            
            // Check ALL attributes - if ANY value has price <= 0, mark Pending
            if ($attrsData && is_array($attrsData)) {
                foreach ($attrsData as $attrIdCheck => $attrData) {
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
}
