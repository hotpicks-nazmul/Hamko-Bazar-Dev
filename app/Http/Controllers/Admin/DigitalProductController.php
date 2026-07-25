<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\DigitalCategory;
use App\Models\LicenseKey;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;

class DigitalProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('digitalCategory', 'category')->digital()->inHouse();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if ($request->category) {
            $query->where('digital_category_id', $request->category);
        }

        if ($request->file_type) {
            $query->where('file_type', $request->file_type);
        }

        if ($request->status !== null && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->requires_license !== null && $request->requires_license !== '') {
            $query->where('requires_license_key', $request->requires_license === 'yes');
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

        $sortField = $request->sort ?? 'created_at';
        $sortDirection = $request->direction ?? 'desc';
        
        $allowedSorts = ['name', 'price', 'created_at', 'sale_price', 'file_size', 'download_count'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->latest();
        }

        $perPage = $request->per_page ?? 10;

        $products = $query->paginate($perPage)->appends($request->query());
        $categories = DigitalCategory::where('status', 'active')->get();
        
        $fileTypes = Product::digital()->inHouse()
            ->whereNotNull('file_type')
            ->distinct()
            ->pluck('file_type')
            ->sort()
            ->values();

        $stats = [
            'total' => Product::digital()->inHouse()->count(),
            'active' => Product::digital()->inHouse()->where('is_active', true)->count(),
            'inactive' => Product::digital()->inHouse()->where('is_active', false)->count(),
            'total_downloads' => DB::table('digital_downloads')->count(),
            'total_sales' => Product::digital()->inHouse()
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.payment_status', 'paid')
                ->sum('order_items.quantity'),
            'total_revenue' => Product::digital()->inHouse()
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.payment_status', 'paid')
                ->sum('order_items.total'),
            'license_based' => Product::digital()->inHouse()->where('requires_license_key', true)->count(),
            'available_licenses' => LicenseKey::whereIn('product_id', 
                Product::digital()->inHouse()->where('requires_license_key', true)->pluck('id')
            )->where('status', 'available')->count(),
        ];

        if ($request->ajax || $request->ajax == '1' || $request->wantsJson()) {
            $html = view('admin.products.partials.digital-product-rows', compact('products'))->render();
            
            $pagination = '';
            if ($products->hasPages()) {
                $pagination = $products->appends($request->query())->links()->toHtml();
            }
            
            return response()->json([
                'html' => $html,
                'pagination' => $pagination,
                'stats' => $stats,
                'total' => $products->total()
            ]);
        }

        return view('admin.products.digital', compact('products', 'categories', 'fileTypes', 'stats'));
    }

    public function create(Request $request)
    {
        $categories = DigitalCategory::getFlattenedTree();
        $preselectedCategory = $request->get('category_id');
        return view('admin.products.digital-create', compact('categories', 'preselectedCategory'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:digital_categories,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|max:100|unique:products',
            'product_code' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:5120',
            'images.*' => 'nullable|image|max:5120',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'digital_file' => 'required_without:download_link|file|max:512000',
            'download_link' => 'nullable|url|max:500',
            'download_limit' => 'nullable|integer|min:0',
            'download_expiry_days' => 'nullable|integer|min:0',
            'version' => 'nullable|string|max:50',
            'license_type' => 'nullable|string|max:100',
            'requires_license_key' => 'boolean',
            'auto_generate_license' => 'boolean',
            'installation_instructions' => 'nullable|string',
            'system_requirements' => 'nullable|string',
            'additional_files.*' => 'nullable|file|max:102400',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->except(['description', 'digital_file', 'additional_files', '_token']);
            $data['slug'] = Str::slug($request->name);
            $data['long_description'] = $request->description;
            $data['is_digital'] = true;
            $data['product_source'] = 'in_house';
            $data['created_by'] = auth()->id();
            $data['quantity'] = 999999;
            $data['digital_category_id'] = $request->category_id;
            
            if ($request->hasFile('image')) {
                if (ImageHelper::isValidImage($request->file('image'))) {
                    $imageResult = ImageHelper::processImage(
                        $request->file('image'),
                        'products',
                        1920,
                        300,
                        85
                    );
                    $data['featured_image'] = $imageResult['path'];
                    $data['thumbnail'] = $imageResult['thumbnail'] ?? null;
                }
            }

            if ($request->hasFile('images')) {
                $gallery = [];
                foreach ($request->file('images') as $galleryImage) {
                    if (ImageHelper::isValidImage($galleryImage)) {
                        $imageResult = ImageHelper::processImage(
                            $galleryImage,
                            'products',
                            1920,
                            300,
                            85
                        );
                        $gallery[] = $imageResult['path'];
                    }
                }
                $data['gallery'] = $gallery;
            }

            if ($request->hasFile('digital_file')) {
                $file = $request->file('digital_file');
                $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
                $filePath = 'digital-products/' . $fileName;
                $file->storeAs('public', $filePath);
                
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_path'] = $filePath;
                $data['file_size'] = $file->getSize();
                $data['file_type'] = $file->getMimeType();
                $data['file_format'] = strtoupper($file->getClientOriginalExtension());
            }

            if ($request->hasFile('additional_files')) {
                $additionalFiles = [];
                foreach ($request->file('additional_files') as $additionalFile) {
                    $fileName = Str::random(40) . '.' . $additionalFile->getClientOriginalExtension();
                    $filePath = 'digital-products/additional/' . $fileName;
                    $additionalFile->storeAs('public', $filePath);
                    
                    $additionalFiles[] = [
                        'name' => $additionalFile->getClientOriginalName(),
                        'path' => $filePath,
                        'size' => $additionalFile->getSize(),
                        'type' => $additionalFile->getMimeType(),
                    ];
                }
                $data['additional_files'] = $additionalFiles;
            }

            $product = Product::create($data);

            if ($request->requires_license_key && $request->auto_generate_license) {
                $licenseCount = $request->license_count ?? 10;
                LicenseKey::generateMultiple($licenseCount, $product->id);
            }

            DB::commit();

            return redirect()->route('admin.products.digital.index')
                ->with('success', 'Digital product created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating product: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $product = Product::with('licenseKeys')->findOrFail($id);
        
        if (!$product->is_digital) {
            return redirect()->route('admin.products.edit', $id)
                ->with('error', 'This is not a digital product.');
        }

        $categories = DigitalCategory::getFlattenedTree();
        
        $licenseStats = [
            'total' => $product->licenseKeys()->count(),
            'available' => $product->licenseKeys()->available()->count(),
            'used' => $product->licenseKeys()->used()->count(),
            'disabled' => $product->licenseKeys()->disabled()->count(),
        ];

        return view('admin.products.digital-edit', compact('product', 'categories', 'licenseStats'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        if (!$product->is_digital) {
            return redirect()->route('admin.products.edit', $id)
                ->with('error', 'This is not a digital product.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:digital_categories,id',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'sku' => 'required|string|max:100|unique:products,sku,' . $id,
            'product_code' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:5120',
            'images.*' => 'nullable|image|max:5120',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'digital_file' => 'nullable|file|max:512000',
            'download_link' => 'nullable|url|max:500',
            'download_limit' => 'nullable|integer|min:0',
            'download_expiry_days' => 'nullable|integer|min:0',
            'version' => 'nullable|string|max:50',
            'license_type' => 'nullable|string|max:100',
            'requires_license_key' => 'boolean',
            'auto_generate_license' => 'boolean',
            'installation_instructions' => 'nullable|string',
            'system_requirements' => 'nullable|string',
            'additional_files.*' => 'nullable|file|max:102400',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->except(['description', 'digital_file', 'additional_files', '_token', '_method']);
            $data['slug'] = Str::slug($request->name);
            $data['long_description'] = $request->description;
            $data['digital_category_id'] = $request->category_id;

            if ($request->hasFile('image')) {
                if ($product->featured_image) {
                    ImageHelper::deleteImage($product->featured_image, $product->featured_thumbnail ?? null);
                }
                if (ImageHelper::isValidImage($request->file('image'))) {
                    $imageResult = ImageHelper::processImage(
                        $request->file('image'),
                        'products',
                        1920,
                        300,
                        85
                    );
                    $data['featured_image'] = $imageResult['path'];
                    $data['thumbnail'] = $imageResult['thumbnail'] ?? null;
                }
            }

            if ($request->hasFile('images')) {
                if ($product->gallery) {
                    foreach ($product->gallery as $oldImage) {
                        ImageHelper::deleteImage($oldImage);
                    }
                }
                
                $gallery = [];
                foreach ($request->file('images') as $galleryImage) {
                    if (ImageHelper::isValidImage($galleryImage)) {
                        $imageResult = ImageHelper::processImage(
                            $galleryImage,
                            'products',
                            1920,
                            300,
                            85
                        );
                        $gallery[] = $imageResult['path'];
                    }
                }
                $data['gallery'] = $gallery;
            }

            if ($request->hasFile('digital_file')) {
                if ($product->file_path) {
                    Storage::delete('public/' . $product->file_path);
                }
                
                $file = $request->file('digital_file');
                $fileName = Str::random(40) . '.' . $file->getClientOriginalExtension();
                $filePath = 'digital-products/' . $fileName;
                $file->storeAs('public', $filePath);
                
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_path'] = $filePath;
                $data['file_size'] = $file->getSize();
                $data['file_type'] = $file->getMimeType();
                $data['file_format'] = strtoupper($file->getClientOriginalExtension());
            }

            if ($request->hasFile('additional_files')) {
                $additionalFiles = $product->additional_files ?? [];
                
                foreach ($request->file('additional_files') as $additionalFile) {
                    $fileName = Str::random(40) . '.' . $additionalFile->getClientOriginalExtension();
                    $filePath = 'digital-products/additional/' . $fileName;
                    $additionalFile->storeAs('public', $filePath);
                    
                    $additionalFiles[] = [
                        'name' => $additionalFile->getClientOriginalName(),
                        'path' => $filePath,
                        'size' => $additionalFile->getSize(),
                        'type' => $additionalFile->getMimeType(),
                    ];
                }
                $data['additional_files'] = $additionalFiles;
            }

            $product->update($data);

            DB::commit();

            return redirect()->route('admin.products.digital.index')
                ->with('success', 'Digital product updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error updating product: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        
        if (!$product->is_digital) {
            return response()->json([
                'success' => false,
                'message' => 'This is not a digital product.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            if ($product->file_path) {
                Storage::delete('public/' . $product->file_path);
            }
            
            if ($product->additional_files) {
                foreach ($product->additional_files as $file) {
                    Storage::delete('public/' . $file['path']);
                }
            }
            
            if ($product->featured_image) {
                ImageHelper::deleteImage($product->featured_image, $product->thumbnail ?? null);
            }
            
            if ($product->gallery) {
                foreach ($product->gallery as $image) {
                    ImageHelper::deleteImage($image);
                }
            }
            
            $product->licenseKeys()->delete();
            
            $product->delete();

            DB::commit();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Digital product deleted successfully.'
                ]);
            }

            return redirect()->route('admin.products.digital.index')
                ->with('success', 'Digital product deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting product: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error deleting product: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);

        return response()->json([
            'success' => true,
            'status' => $product->is_active ? 'active' : 'inactive',
            'message' => 'Product status updated.'
        ]);
    }

    public function toggleFeatured($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_featured' => !$product->is_featured]);

        return response()->json([
            'success' => true,
            'featured' => $product->is_featured,
            'message' => 'Product featured status updated.'
        ]);
    }

    public function generateLicenseKeys(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        if (!$product->requires_license_key) {
            return response()->json([
                'success' => false,
                'message' => 'This product does not require license keys.'
            ], 400);
        }

        $request->validate([
            'count' => 'required|integer|min:1|max:1000',
            'format' => 'nullable|string|max:50',
        ]);

        $format = $request->format ?? 'XXXX-XXXX-XXXX-XXXX';
        $keys = LicenseKey::generateMultiple($request->count, $id, $format);

        return response()->json([
            'success' => true,
            'count' => count($keys),
            'message' => $request->count . ' license keys generated successfully.'
        ]);
    }

    public function getLicenseKeys(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        
        $query = $product->licenseKeys();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $licenseKeys = $query->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $licenseKeys,
        ]);
    }

    public function exportLicenseKeys($id)
    {
        $product = Product::findOrFail($id);
        
        $licenseKeys = $product->licenseKeys()
            ->orderBy('created_at', 'desc')
            ->get();

        $csv = "License Key,Status,Assigned To,Assigned At,Expires At\n";
        
        foreach ($licenseKeys as $key) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s\n",
                $key->license_key,
                $key->status,
                $key->user ? $key->user->name : '-',
                $key->assigned_at ? $key->assigned_at->format('Y-m-d H:i') : '-',
                $key->expires_at ? $key->expires_at->format('Y-m-d') : '-'
            );
        }

        $filename = Str::slug($product->name) . '-license-keys.csv';

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function deleteLicenseKey($id, $keyId)
    {
        $licenseKey = LicenseKey::where('product_id', $id)->findOrFail($keyId);
        
        if ($licenseKey->status === 'used') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a used license key.'
            ], 400);
        }

        $licenseKey->delete();

        return response()->json([
            'success' => true,
            'message' => 'License key deleted successfully.'
        ]);
    }

    public function disableLicenseKey($id, $keyId)
    {
        $licenseKey = LicenseKey::where('product_id', $id)->findOrFail($keyId);
        $licenseKey->disable();

        return response()->json([
            'success' => true,
            'message' => 'License key disabled successfully.'
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'ids' => 'required|string',
        ]);

        $ids = json_decode($request->ids, true);
        
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No products selected.');
        }

        $products = Product::digital()->whereIn('id', $ids)->get();

        switch ($request->action) {
            case 'activate':
                $products->each->update(['is_active' => true]);
                $message = count($products) . ' products activated successfully.';
                break;
                
            case 'deactivate':
                $products->each->update(['is_active' => false]);
                $message = count($products) . ' products deactivated successfully.';
                break;
                
            case 'delete':
                foreach ($products as $product) {
                    if ($product->file_path) {
                        Storage::delete('public/' . $product->file_path);
                    }
                    if ($product->additional_files) {
                        foreach ($product->additional_files as $file) {
                            Storage::delete('public/' . $file['path']);
                        }
                    }
                    if ($product->featured_image) {
                        ImageHelper::deleteImage($product->featured_image, $product->featured_thumbnail ?? null);
                    }
                    if ($product->gallery) {
                        foreach ($product->gallery as $image) {
                            ImageHelper::deleteImage($image);
                        }
                    }
                    $product->licenseKeys()->delete();
                    $product->delete();
                }
                $message = count($products) . ' products deleted successfully.';
                break;
                
            case 'feature':
                $products->each->update(['is_featured' => true]);
                $message = count($products) . ' products featured successfully.';
                break;
                
            case 'unfeature':
                $products->each->update(['is_featured' => false]);
                $message = count($products) . ' products unfeatured successfully.';
                break;
                
            default:
                return redirect()->back()->with('error', 'Invalid action.');
        }

        return redirect()->back()->with('success', $message);
    }

    public function export(Request $request)
    {
        $products = Product::digital()->inHouse()
            ->with('category')
            ->orderBy('created_at', 'desc')
            ->get();

        $csv = "Name,SKU,Category,Price,Sale Price,File Format,File Size,Downloads,Status,Created At\n";
        
        foreach ($products as $product) {
            $csv .= sprintf(
                "\"%s\",%s,%s,%s,%s,%s,%s,%d,%s,%s\n",
                $product->name,
                $product->sku,
                $product->category->name ?? '-',
                $product->price,
                $product->sale_price ?? '-',
                $product->file_format ?? '-',
                $product->file_size_formatted,
                $product->digitalDownloads()->count(),
                $product->is_active ? 'Active' : 'Inactive',
                $product->created_at->format('Y-m-d')
            );
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="digital-products.csv"',
        ]);
    }

    public function deleteAdditionalFile($id, $index)
    {
        $product = Product::findOrFail($id);
        
        $additionalFiles = $product->additional_files ?? [];
        
        if (!isset($additionalFiles[$index])) {
            return response()->json([
                'success' => false,
                'message' => 'File not found.'
            ], 404);
        }

        Storage::delete('public/' . $additionalFiles[$index]['path']);
        
        unset($additionalFiles[$index]);
        $additionalFiles = array_values($additionalFiles);
        
        $product->update(['additional_files' => $additionalFiles]);

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully.'
        ]);
    }

    public function downloadStats($id)
    {
        $product = Product::findOrFail($id);
        
        $stats = [
            'total_downloads' => $product->digitalDownloads()->count(),
            'unique_downloads' => $product->digitalDownloads()->distinct('user_id')->count('user_id'),
            'recent_downloads' => $product->digitalDownloads()
                ->with('user:id,name,email')
                ->orderBy('last_download_at', 'desc')
                ->limit(10)
                ->get(),
            'downloads_by_date' => $product->digitalDownloads()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
