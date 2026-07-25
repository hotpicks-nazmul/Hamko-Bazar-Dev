<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ImageHelper;

class BrandController extends Controller
{
    /**
     * Display brands list with statistics.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $featured = $request->get('featured');
        $sort = $request->get('sort', 'sort_order');
        $direction = $request->get('direction', 'asc');
        $perPage = $request->get('per_page', 25);
        
        // Statistics
        $stats = [
            'total' => Brand::count(),
            'active' => Brand::where('is_active', true)->count(),
            'inactive' => Brand::where('is_active', false)->count(),
            'featured' => Brand::where('is_featured', true)->count(),
            'with_products' => Brand::has('products')->count(),
        ];
        
        // Build query
        $query = Brand::withCount('products');
        
        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($status !== null && $status !== '') {
            $query->where('is_active', $status === 'active');
        }
        
        // Featured filter
        if ($featured !== null && $featured !== '') {
            $query->where('is_featured', $featured === 'yes');
        }
        
        // Sorting
        $validSorts = ['name', 'sort_order', 'created_at', 'products_count'];
        if (in_array($sort, $validSorts)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('sort_order')->orderBy('name');
        }
        
        $brands = $query->paginate($perPage)->appends($request->query());
        
        // AJAX response
        if ($request->ajax || $request->ajax() || $request->wantsJson()) {
            $html = view('admin.brands.partials.table-rows', compact('brands'))->render();
            
            return response()->json([
                'html' => $html,
                'stats' => $stats,
                'pagination' => $brands->links()->toHtml(),
                'total' => $brands->total()
            ]);
        }
        
        return view('admin.brands.index', compact('brands', 'stats'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.brands.create');
    }

    /**
     * Store new brand.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'logo' => 'nullable|image|max:5120',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);
        
        $data = $request->only([
            'name', 'slug', 'description', 'website', 
            'is_active', 'is_featured', 'sort_order',
            'meta_title', 'meta_description', 'meta_keywords'
        ]);
        
        // Handle checkbox values
        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        // Upload logo with WebP conversion
        if ($request->hasFile('logo')) {
            if (ImageHelper::isValidImage($request->file('logo'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('logo'),
                    'brands',        // directory
                    400,             // max width (brand logo)
                    150,             // thumbnail width
                    85               // quality
                );
                $data['logo'] = $imageResult['path'];
            }
        }
        
        $brand = Brand::create($data);
        
        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand created successfully.');
    }

    /**
     * Show brand details.
     */
    public function show(Brand $brand)
    {
        $brand->loadCount('products');
        $products = $brand->products()->paginate(10);
        
        return view('admin.brands.show', compact('brand', 'products'));
    }

    /**
     * Show edit form.
     */
    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    /**
     * Update brand.
     */
    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $brand->id,
            'logo' => 'nullable|image|max:5120',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);
        
        $data = $request->only([
            'name', 'slug', 'description', 'website',
            'sort_order', 'meta_title', 'meta_description', 'meta_keywords'
        ]);
        
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        
        // Handle checkbox values
        $data['is_active'] = $request->has('is_active');
        $data['is_featured'] = $request->has('is_featured');
        
        // Upload new logo with WebP conversion
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($brand->logo) {
                ImageHelper::deleteImage($brand->logo);
            }
            if (ImageHelper::isValidImage($request->file('logo'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('logo'),
                    'brands',        // directory
                    400,             // max width (brand logo)
                    150,             // thumbnail width
                    85               // quality
                );
                $data['logo'] = $imageResult['path'];
            }
        }
        
        // Remove logo if requested
        if ($request->has('remove_logo') && $brand->logo) {
            ImageHelper::deleteImage($brand->logo);
            $data['logo'] = null;
        }
        
        $brand->update($data);
        
        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    /**
     * Delete brand.
     */
    public function destroy(Brand $brand)
    {
        // Check if brand has products
        if ($brand->products()->exists()) {
            return redirect()->route('admin.brands.index')
                ->with('error', 'Cannot delete brand with associated products. Please reassign products first.');
        }
        
        // Delete logo
        if ($brand->logo) {
            ImageHelper::deleteImage($brand->logo);
        }
        
        $brand->delete();
        
        return redirect()->route('admin.brands.index')
            ->with('success', 'Brand deleted successfully.');
    }

    /**
     * Toggle brand status.
     */
    public function toggleStatus(Brand $brand)
    {
        $brand->update([
            'is_active' => !$brand->is_active
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Brand status updated.',
            'is_active' => $brand->is_active
        ]);
    }

    /**
     * Toggle brand featured status.
     */
    public function toggleFeatured(Brand $brand)
    {
        $brand->update([
            'is_featured' => !$brand->is_featured
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Brand featured status updated.',
            'is_featured' => $brand->is_featured
        ]);
    }

    /**
     * Bulk actions.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,feature,unfeature,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:brands,id',
        ]);
        
        $ids = $request->ids;
        $action = $request->action;
        
        switch ($action) {
            case 'activate':
                Brand::whereIn('id', $ids)->update(['is_active' => true]);
                $message = count($ids) . ' brand(s) activated successfully.';
                break;
                
            case 'deactivate':
                Brand::whereIn('id', $ids)->update(['is_active' => false]);
                $message = count($ids) . ' brand(s) deactivated successfully.';
                break;
                
            case 'feature':
                Brand::whereIn('id', $ids)->update(['is_featured' => true]);
                $message = count($ids) . ' brand(s) featured successfully.';
                break;
                
            case 'unfeature':
                Brand::whereIn('id', $ids)->update(['is_featured' => false]);
                $message = count($ids) . ' brand(s) unfeatured successfully.';
                break;
                
            case 'delete':
                // Check for products before deleting
                $brandsWithProducts = Brand::whereIn('id', $ids)->has('products')->count();
                if ($brandsWithProducts > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot delete {$brandsWithProducts} brand(s) with associated products."
                    ], 400);
                }
                
                // Delete logos
                $brands = Brand::whereIn('id', $ids)->whereNotNull('logo')->get();
                foreach ($brands as $brand) {
                    ImageHelper::deleteImage($brand->logo);
                }
                
                Brand::whereIn('id', $ids)->delete();
                $message = count($ids) . ' brand(s) deleted successfully.';
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Export brands.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        
        $brands = Brand::withCount('products')
            ->orderBy('name')
            ->get();
        
        if ($format === 'csv') {
            $filename = 'brands_' . date('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function () use ($brands) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['ID', 'Name', 'Slug', 'Website', 'Status', 'Featured', 'Products Count', 'Created At']);
                
                foreach ($brands as $brand) {
                    fputcsv($file, [
                        $brand->id,
                        $brand->name,
                        $brand->slug,
                        $brand->website,
                        $brand->is_active ? 'Active' : 'Inactive',
                        $brand->is_featured ? 'Yes' : 'No',
                        $brand->products_count,
                        $brand->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }
        
        return redirect()->route('admin.brands.index');
    }

    /**
     * Get brands for select dropdown (API).
     */
    public function getBrands(Request $request)
    {
        $search = $request->get('search');
        
        $query = Brand::where('is_active', true);
        
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        $brands = $query->orderBy('name')->limit(50)->get(['id', 'name', 'logo']);
        
        return response()->json([
            'brands' => $brands
        ]);
    }
}
