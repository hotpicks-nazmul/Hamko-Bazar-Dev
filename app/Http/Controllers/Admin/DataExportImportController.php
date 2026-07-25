<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Color;
use App\Models\Coupon;
use App\Models\User;
use App\Models\Banner;
use App\Models\Slider;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Subscriber;
use App\Models\ProductBundle;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Tax;
use App\Models\DeliveryZone;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DataExportImportController extends Controller
{
    /**
     * Display the data export/import page.
     */
    public function index()
    {
        // Get counts for each entity type
        $counts = [
            'products' => Product::count(),
            'categories' => Category::count(),
            'brands' => Brand::count(),
            'attributes' => Attribute::count(),
            'colors' => Color::count(),
            'coupons' => Coupon::count(),
            'users' => User::count(),
            'banners' => Banner::count(),
            'sliders' => Slider::count(),
            'blogs' => Blog::count(),
            'subscribers' => Subscriber::count(),
            'product_bundles' => ProductBundle::count(),
            'faqs' => Faq::count(),
            'pages' => Page::count(),
            'taxes' => Tax::count(),
            'delivery_zones' => DeliveryZone::count(),
            'warehouses' => Warehouse::count(),
        ];

        return view('admin.system.data-export-import', compact('counts'));
    }

    /**
     * Export data to CSV or JSON format.
     */
    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:products,categories,brands,attributes,colors,coupons,users,banners,sliders,blogs,subscribers,product_bundles,faqs,pages,taxes,delivery_zones,warehouses',
            'format' => 'nullable|in:csv,json',
        ]);

        $type = $request->type;
        $format = $request->format ?? 'csv';

        $data = $this->getExportData($type);

        if ($data->isEmpty()) {
            return back()->with('error', 'No data available for export.');
        }

        $filename = $type . '-export-' . date('Y-m-d-His');

        if ($format === 'json') {
            return response()->json($data)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '.json"');
        }

        // CSV Export
        return response()->stream(function () use ($data) {
            $file = fopen('php://output', 'w');
            
            if ($data->isNotEmpty()) {
                // Get column headers from first item
                fputcsv($file, array_keys($data->first()->toArray()));
                
                // Add data rows
                foreach ($data as $row) {
                    fputcsv($file, $row->toArray());
                }
            }
            
            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ]);
    }

    /**
     * Get export data based on type.
     */
    private function getExportData($type)
    {
        switch ($type) {
            case 'products':
                return Product::select([
                    'id', 'name', 'slug', 'sku', 'category_id', 'brand_id',
                    'price', 'purchase_price', 'discount_price', 'quantity',
                    'description', 'short_description', 'featured_image',
                    'gallery_images', 'is_featured', 'is_active', 'is_digital',
                    'digital_file', 'meta_title', 'meta_description', 'created_at', 'updated_at'
                ])->with('category:id,name', 'brand:id,name')->get();

            case 'categories':
                return Category::select([
                    'id', 'name', 'slug', 'parent_id', 'icon', 'image',
                    'description', 'meta_title', 'meta_description', 'order_level',
                    'is_featured', 'status', 'created_at', 'updated_at'
                ])->with('parent:id,name')->get();

            case 'brands':
                return Brand::select([
                    'id', 'name', 'slug', 'logo', 'meta_title', 'meta_description',
                    'is_featured', 'status', 'created_at', 'updated_at'
                ])->get();

            case 'attributes':
                return Attribute::select([
                    'id', 'name', 'slug', 'status', 'created_at', 'updated_at'
                ])->with('values:id,attribute_id,name,value')->get();

            case 'colors':
                return Color::select([
                    'id', 'name', 'code', 'status', 'created_at', 'updated_at'
                ])->get();

            case 'coupons':
                return Coupon::select([
                    'id', 'code', 'type', 'value', 'min_buy', 'max_discount',
                    'start_date', 'end_date', 'is_first_order', 'is_public',
                    'is_active', 'created_at', 'updated_at'
                ])->get();

            case 'users':
                return User::select([
                    'id', 'name', 'email', 'phone', 'avatar', 'provider',
                    'provider_id', 'email_verified_at', 'is_active', 'is_customer',
                    'is_seller', 'is_admin', 'created_at', 'updated_at'
                ])->get();

            case 'banners':
                return Banner::select([
                    'id', 'title', 'url', 'image', 'position', 'is_published',
                    'start_date', 'end_date', 'created_at', 'updated_at'
                ])->get();

            case 'sliders':
                return Slider::select([
                    'id', 'title', 'subtitle', 'text', 'image', 'link',
                    'link_text', 'is_active', 'order', 'created_at', 'updated_at'
                ])->get();

            case 'blogs':
                return Blog::select([
                    'id', 'title', 'slug', 'category_id', 'author_id',
                    'featured_image', 'short_description', 'content',
                    'meta_title', 'meta_description', 'is_published',
                    'published_at', 'created_at', 'updated_at'
                ])->with('category:id,name', 'author:id,name')->get();

            case 'subscribers':
                return Subscriber::select([
                    'id', 'email', 'is_active', 'created_at', 'updated_at'
                ])->get();

            case 'product_bundles':
                return ProductBundle::select([
                    'id', 'title', 'slug', 'sku', 'discount_type', 'discount_value',
                    'start_date', 'end_date', 'is_featured', 'is_active',
                    'created_at', 'updated_at'
                ])->get();

            case 'faqs':
                return Faq::select([
                    'id', 'question', 'answer', 'category', 'is_active',
                    'created_at', 'updated_at'
                ])->get();

            case 'pages':
                return Page::select([
                    'id', 'title', 'slug', 'content', 'meta_title', 'meta_description',
                    'is_published', 'created_at', 'updated_at'
                ])->get();

            case 'taxes':
                return Tax::select([
                    'id', 'name', 'rate', 'is_active', 'created_at', 'updated_at'
                ])->get();

            case 'delivery_zones':
                return DeliveryZone::select([
                    'id', 'name', 'cost', 'free_shipping', 'min_shipping_amount',
                    'is_active', 'created_at', 'updated_at'
                ])->get();

            case 'warehouses':
                return Warehouse::select([
                    'id', 'name', 'address', 'phone', 'is_default', 'is_active',
                    'created_at', 'updated_at'
                ])->get();

            default:
                return collect([]);
        }
    }

    /**
     * Import data from CSV or JSON file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'type' => 'required|in:products,categories,brands,attributes,colors,coupons,users,banners,sliders,blogs,subscribers,product_bundles,faqs,pages,taxes,delivery_zones,warehouses',
            'file' => 'required|file|mimes:csv,json',
            'action' => 'nullable|in:create,update,both',
        ]);

        $type = $request->type;
        $action = $request->action ?? 'create';
        $file = $request->file('file');

        // Read file contents
        if ($file->getClientOriginalExtension() === 'json') {
            $data = json_decode(file_get_contents($file->getRealPath()), true);
            if (!is_array($data)) {
                return back()->with('error', 'Invalid JSON format.');
            }
            $data = collect($data);
        } else {
            // CSV
            $data = $this->parseCSV($file->getRealPath());
        }

        if ($data->isEmpty()) {
            return back()->with('error', 'No data found in the file.');
        }

        // Import data based on type
        $result = $this->processImport($type, $data, $action);

        if ($result['success']) {
            return back()->with('success', "Successfully imported {$result['count']} records. {$result['created']} created, {$result['updated']} updated.");
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Parse CSV file to collection.
     */
    private function parseCSV($filePath)
    {
        $data = [];
        $handle = fopen($filePath, 'r');
        
        // Get headers
        $headers = fgetcsv($handle);
        
        while (($row = fgetcsv($handle)) !== false) {
            $data[] = array_combine($headers, $row);
        }
        
        fclose($handle);
        
        return collect($data);
    }

    /**
     * Process import based on type.
     */
    private function processImport($type, $data, $action)
    {
        try {
            $created = 0;
            $updated = 0;

            switch ($type) {
                case 'categories':
                    foreach ($data as $row) {
                        $result = $this->importCategory($row, $action);
                        if ($result === 'created') $created++;
                        if ($result === 'updated') $updated++;
                    }
                    break;

                case 'brands':
                    foreach ($data as $row) {
                        $result = $this->importBrand($row, $action);
                        if ($result === 'created') $created++;
                        if ($result === 'updated') $updated++;
                    }
                    break;

                case 'attributes':
                    foreach ($data as $row) {
                        $result = $this->importAttribute($row, $action);
                        if ($result === 'created') $created++;
                        if ($result === 'updated') $updated++;
                    }
                    break;

                case 'colors':
                    foreach ($data as $row) {
                        $result = $this->importColor($row, $action);
                        if ($result === 'created') $created++;
                        if ($result === 'updated') $updated++;
                    }
                    break;

                case 'coupons':
                    foreach ($data as $row) {
                        $result = $this->importCoupon($row, $action);
                        if ($result === 'created') $created++;
                        if ($result === 'updated') $updated++;
                    }
                    break;

                case 'banners':
                    foreach ($data as $row) {
                        $result = $this->importBanner($row, $action);
                        if ($result === 'created') $created++;
                        if ($result === 'updated') $updated++;
                    }
                    break;

                case 'sliders':
                    foreach ($data as $row) {
                        $result = $this->importSlider($row, $action);
                        if ($result === 'created') $created++;
                        if ($result === 'updated') $updated++;
                    }
                    break;

                case 'faqs':
                    foreach ($data as $row) {
                        $result = $this->importFaq($row, $action);
                        if ($result === 'created') $created++;
                        if ($result === 'updated') $updated++;
                    }
                    break;

                case 'pages':
                    foreach ($data as $row) {
                        $result = $this->importPage($row, $action);
                        if ($result === 'created') $created++;
                        if ($result === 'updated') $updated++;
                    }
                    break;

                case 'taxes':
                    foreach ($data as $row) {
                        $result = $this->importTax($row, $action);
                        if ($result === 'created') $created++;
                        if ($result === 'updated') $updated++;
                    }
                    break;

                case 'delivery_zones':
                    foreach ($data as $row) {
                        $result = $this->importDeliveryZone($row, $action);
                        if ($result === 'created') $created++;
                        if ($result === 'updated') $updated++;
                    }
                    break;

                case 'warehouses':
                    foreach ($data as $row) {
                        $result = $this->importWarehouse($row, $action);
                        if ($result === 'created') $created++;
                        if ($result === 'updated') $updated++;
                    }
                    break;

                case 'subscribers':
                    foreach ($data as $row) {
                        $result = $this->importSubscriber($row, $action);
                        if ($result === 'created') $created++;
                        if ($result === 'updated') $updated++;
                    }
                    break;

                case 'users':
                    foreach ($data as $row) {
                        $result = $this->importUser($row, $action);
                        if ($result === 'created') $created++;
                        if ($result === 'updated') $updated++;
                    }
                    break;

                // Complex imports - create-only for now
                case 'products':
                    foreach ($data as $row) {
                        $result = $this->importProduct($row, $action);
                        if ($result === 'created') $created++;
                    }
                    break;

                case 'blogs':
                    foreach ($data as $row) {
                        $result = $this->importBlog($row, $action);
                        if ($result === 'created') $created++;
                    }
                    break;

                case 'product_bundles':
                    foreach ($data as $row) {
                        $result = $this->importProductBundle($row, $action);
                        if ($result === 'created') $created++;
                    }
                    break;

                default:
                    return ['success' => false, 'message' => 'Import not supported for this type.'];
            }

            return [
                'success' => true,
                'count' => $created + $updated,
                'created' => $created,
                'updated' => $updated,
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // Import methods for each entity type

    private function importCategory($data, $action)
    {
        $name = $data['name'] ?? null;
        if (!$name) return null;

        $slug = $data['slug'] ?? Str::slug($name);
        
        $query = Category::where('slug', $slug);
        
        if ($action === 'update' || $action === 'both') {
            $category = $query->first();
            if ($category) {
                $category->update([
                    'name' => $data['name'] ?? $category->name,
                    'description' => $data['description'] ?? $category->description,
                    'meta_title' => $data['meta_title'] ?? $category->meta_title,
                    'meta_description' => $data['meta_description'] ?? $category->meta_description,
                    'is_featured' => isset($data['is_featured']) ? ($data['is_featured'] === '1' || $data['is_featured'] === 'true') : $category->is_featured,
                    'status' => $data['status'] ?? $category->status,
                ]);
                return 'updated';
            }
        }
        
        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                Category::create([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $data['description'] ?? null,
                    'meta_title' => $data['meta_title'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'is_featured' => $data['is_featured'] ?? false,
                    'status' => $data['status'] ?? 'active',
                ]);
                return 'created';
            }
        }
        
        return null;
    }

    private function importBrand($data, $action)
    {
        $name = $data['name'] ?? null;
        if (!$name) return null;

        $slug = $data['slug'] ?? Str::slug($name);
        
        $query = Brand::where('slug', $slug);
        
        if ($action === 'update' || $action === 'both') {
            $brand = $query->first();
            if ($brand) {
                $brand->update([
                    'name' => $data['name'] ?? $brand->name,
                    'meta_title' => $data['meta_title'] ?? $brand->meta_title,
                    'meta_description' => $data['meta_description'] ?? $brand->meta_description,
                    'is_featured' => isset($data['is_featured']) ? ($data['is_featured'] === '1' || $data['is_featured'] === 'true') : $brand->is_featured,
                    'status' => $data['status'] ?? $brand->status,
                ]);
                return 'updated';
            }
        }
        
        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                Brand::create([
                    'name' => $name,
                    'slug' => $slug,
                    'meta_title' => $data['meta_title'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'is_featured' => $data['is_featured'] ?? false,
                    'status' => $data['status'] ?? 'active',
                ]);
                return 'created';
            }
        }
        
        return null;
    }

    private function importAttribute($data, $action)
    {
        $name = $data['name'] ?? null;
        if (!$name) return null;

        $slug = $data['slug'] ?? Str::slug($name);
        
        $query = Attribute::where('slug', $slug);
        
        if ($action === 'update' || $action === 'both') {
            $attribute = $query->first();
            if ($attribute) {
                $attribute->update([
                    'name' => $data['name'] ?? $attribute->name,
                    'status' => $data['status'] ?? $attribute->status,
                ]);
                return 'updated';
            }
        }
        
        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                $attribute = Attribute::create([
                    'name' => $name,
                    'slug' => $slug,
                    'status' => $data['status'] ?? 'active',
                ]);
                return 'created';
            }
        }
        
        return null;
    }

    private function importColor($data, $action)
    {
        $name = $data['name'] ?? null;
        if (!$name) return null;

        $code = $data['code'] ?? '#000000';
        
        $query = Color::where('code', $code);
        
        if ($action === 'update' || $action === 'both') {
            $color = $query->first();
            if ($color) {
                $color->update([
                    'name' => $data['name'] ?? $color->name,
                    'status' => $data['status'] ?? $color->status,
                ]);
                return 'updated';
            }
        }
        
        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                Color::create([
                    'name' => $name,
                    'code' => $code,
                    'status' => $data['status'] ?? 'active',
                ]);
                return 'created';
            }
        }
        
        return null;
    }

    private function importCoupon($data, $action)
    {
        $code = $data['code'] ?? null;
        if (!$code) return null;

        $query = Coupon::where('code', $code);
        
        if ($action === 'update' || $action === 'both') {
            $coupon = $query->first();
            if ($coupon) {
                $coupon->update([
                    'type' => $data['type'] ?? $coupon->type,
                    'value' => $data['value'] ?? $coupon->value,
                    'min_buy' => $data['min_buy'] ?? $coupon->min_buy,
                    'max_discount' => $data['max_discount'] ?? $coupon->max_discount,
                    'start_date' => $data['start_date'] ?? $coupon->start_date,
                    'end_date' => $data['end_date'] ?? $coupon->end_date,
                    'is_first_order' => isset($data['is_first_order']) ? ($data['is_first_order'] === '1' || $data['is_first_order'] === 'true') : $coupon->is_first_order,
                    'is_public' => isset($data['is_public']) ? ($data['is_public'] === '1' || $data['is_public'] === 'true') : $coupon->is_public,
                    'is_active' => isset($data['is_active']) ? ($data['is_active'] === '1' || $data['is_active'] === 'true') : $coupon->is_active,
                ]);
                return 'updated';
            }
        }
        
        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                Coupon::create([
                    'code' => $code,
                    'type' => $data['type'] ?? 'percent',
                    'value' => $data['value'] ?? 0,
                    'min_buy' => $data['min_buy'] ?? 0,
                    'max_discount' => $data['max_discount'] ?? 0,
                    'start_date' => $data['start_date'] ?? now(),
                    'end_date' => $data['end_date'] ?? now()->addDays(30),
                    'is_first_order' => $data['is_first_order'] ?? false,
                    'is_public' => $data['is_public'] ?? true,
                    'is_active' => $data['is_active'] ?? true,
                ]);
                return 'created';
            }
        }
        
        return null;
    }

    private function importBanner($data, $action)
    {
        $title = $data['title'] ?? null;
        if (!$title) return null;

        $query = Banner::where('title', $title);
        
        if ($action === 'update' || $action === 'both') {
            $banner = $query->first();
            if ($banner) {
                $banner->update([
                    'url' => $data['url'] ?? $banner->url,
                    'image' => $data['image'] ?? $banner->image,
                    'position' => $data['position'] ?? $banner->position,
                    'is_published' => isset($data['is_published']) ? ($data['is_published'] === '1' || $data['is_published'] === 'true') : $banner->is_published,
                ]);
                return 'updated';
            }
        }
        
        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                Banner::create([
                    'title' => $title,
                    'url' => $data['url'] ?? null,
                    'image' => $data['image'] ?? null,
                    'position' => $data['position'] ?? 1,
                    'is_published' => $data['is_published'] ?? true,
                ]);
                return 'created';
            }
        }
        
        return null;
    }

    private function importSlider($data, $action)
    {
        $title = $data['title'] ?? null;
        if (!$title) return null;

        $query = Slider::where('title', $title);
        
        if ($action === 'update' || $action === 'both') {
            $slider = $query->first();
            if ($slider) {
                $slider->update([
                    'subtitle' => $data['subtitle'] ?? $slider->subtitle,
                    'text' => $data['text'] ?? $slider->text,
                    'image' => $data['image'] ?? $slider->image,
                    'link' => $data['link'] ?? $slider->link,
                    'link_text' => $data['link_text'] ?? $slider->link_text,
                    'is_active' => isset($data['is_active']) ? ($data['is_active'] === '1' || $data['is_active'] === 'true') : $slider->is_active,
                    'order' => $data['order'] ?? $slider->order,
                ]);
                return 'updated';
            }
        }
        
        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                Slider::create([
                    'title' => $title,
                    'subtitle' => $data['subtitle'] ?? null,
                    'text' => $data['text'] ?? null,
                    'image' => $data['image'] ?? null,
                    'link' => $data['link'] ?? null,
                    'link_text' => $data['link_text'] ?? null,
                    'is_active' => $data['is_active'] ?? true,
                    'order' => $data['order'] ?? 0,
                ]);
                return 'created';
            }
        }
        
        return null;
    }

    private function importFaq($data, $action)
    {
        $question = $data['question'] ?? null;
        if (!$question) return null;

        $query = Faq::where('question', $question);
        
        if ($action === 'update' || $action === 'both') {
            $faq = $query->first();
            if ($faq) {
                $faq->update([
                    'answer' => $data['answer'] ?? $faq->answer,
                    'category' => $data['category'] ?? $faq->category,
                    'is_active' => isset($data['is_active']) ? ($data['is_active'] === '1' || $data['is_active'] === 'true') : $faq->is_active,
                ]);
                return 'updated';
            }
        }
        
        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                Faq::create([
                    'question' => $question,
                    'answer' => $data['answer'] ?? null,
                    'category' => $data['category'] ?? 'general',
                    'is_active' => $data['is_active'] ?? true,
                ]);
                return 'created';
            }
        }
        
        return null;
    }

    private function importPage($data, $action)
    {
        $title = $data['title'] ?? null;
        if (!$title) return null;

        $slug = $data['slug'] ?? Str::slug($title);
        
        $query = Page::where('slug', $slug);
        
        if ($action === 'update' || $action === 'both') {
            $page = $query->first();
            if ($page) {
                $page->update([
                    'content' => $data['content'] ?? $page->content,
                    'meta_title' => $data['meta_title'] ?? $page->meta_title,
                    'meta_description' => $data['meta_description'] ?? $page->meta_description,
                    'is_published' => isset($data['is_published']) ? ($data['is_published'] === '1' || $data['is_published'] === 'true') : $page->is_published,
                ]);
                return 'updated';
            }
        }
        
        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                Page::create([
                    'title' => $title,
                    'slug' => $slug,
                    'content' => $data['content'] ?? null,
                    'meta_title' => $data['meta_title'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'is_published' => $data['is_published'] ?? false,
                ]);
                return 'created';
            }
        }
        
        return null;
    }

    private function importTax($data, $action)
    {
        $name = $data['name'] ?? null;
        if (!$name) return null;

        $query = Tax::where('name', $name);
        
        if ($action === 'update' || $action === 'both') {
            $tax = $query->first();
            if ($tax) {
                $tax->update([
                    'rate' => $data['rate'] ?? $tax->rate,
                    'is_active' => isset($data['is_active']) ? ($data['is_active'] === '1' || $data['is_active'] === 'true') : $tax->is_active,
                ]);
                return 'updated';
            }
        }
        
        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                Tax::create([
                    'name' => $name,
                    'rate' => $data['rate'] ?? 0,
                    'is_active' => $data['is_active'] ?? true,
                ]);
                return 'created';
            }
        }
        
        return null;
    }

    private function importDeliveryZone($data, $action)
    {
        $name = $data['name'] ?? null;
        if (!$name) return null;

        $query = DeliveryZone::where('name', $name);
        
        if ($action === 'update' || $action === 'both') {
            $zone = $query->first();
            if ($zone) {
                $zone->update([
                    'cost' => $data['cost'] ?? $zone->cost,
                    'free_shipping' => isset($data['free_shipping']) ? ($data['free_shipping'] === '1' || $data['free_shipping'] === 'true') : $zone->free_shipping,
                    'min_shipping_amount' => $data['min_shipping_amount'] ?? $zone->min_shipping_amount,
                    'is_active' => isset($data['is_active']) ? ($data['is_active'] === '1' || $data['is_active'] === 'true') : $zone->is_active,
                ]);
                return 'updated';
            }
        }
        
        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                DeliveryZone::create([
                    'name' => $name,
                    'cost' => $data['cost'] ?? 0,
                    'free_shipping' => $data['free_shipping'] ?? false,
                    'min_shipping_amount' => $data['min_shipping_amount'] ?? 0,
                    'is_active' => $data['is_active'] ?? true,
                ]);
                return 'created';
            }
        }
        
        return null;
    }

    private function importWarehouse($data, $action)
    {
        $name = $data['name'] ?? null;
        if (!$name) return null;

        $query = Warehouse::where('name', $name);
        
        if ($action === 'update' || $action === 'both') {
            $warehouse = $query->first();
            if ($warehouse) {
                $warehouse->update([
                    'address' => $data['address'] ?? $warehouse->address,
                    'phone' => $data['phone'] ?? $warehouse->phone,
                    'is_default' => isset($data['is_default']) ? ($data['is_default'] === '1' || $data['is_default'] === 'true') : $warehouse->is_default,
                    'is_active' => isset($data['is_active']) ? ($data['is_active'] === '1' || $data['is_active'] === 'true') : $warehouse->is_active,
                ]);
                return 'updated';
            }
        }
        
        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                Warehouse::create([
                    'name' => $name,
                    'address' => $data['address'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'is_default' => $data['is_default'] ?? false,
                    'is_active' => $data['is_active'] ?? true,
                ]);
                return 'created';
            }
        }
        
        return null;
    }

    private function importSubscriber($data, $action)
    {
        $email = $data['email'] ?? null;
        if (!$email) return null;

        $query = Subscriber::where('email', $email);
        
        if ($action === 'update' || $action === 'both') {
            $subscriber = $query->first();
            if ($subscriber) {
                $subscriber->update([
                    'is_active' => isset($data['is_active']) ? ($data['is_active'] === '1' || $data['is_active'] === 'true') : $subscriber->is_active,
                ]);
                return 'updated';
            }
        }
        
        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                Subscriber::create([
                    'email' => $email,
                    'is_active' => $data['is_active'] ?? true,
                ]);
                return 'created';
            }
        }
        
        return null;
    }

    private function importUser($data, $action)
    {
        $email = $data['email'] ?? null;
        if (!$email) return null;

        $query = User::where('email', $email);

        if ($action === 'update' || $action === 'both') {
            $user = $query->first();
            if ($user) {
                $updateData = [
                    'name' => $data['name'] ?? $user->name,
                    'phone' => $data['phone'] ?? $user->phone,
                    'is_active' => isset($data['is_active']) ? ($data['is_active'] === '1' || $data['is_active'] === 'true') : $user->is_active,
                ];

                if (!empty($data['password'])) {
                    $updateData['password'] = bcrypt($data['password']);
                }

                $user->update($updateData);
                return 'updated';
            }
        }

        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                $randomPassword = Str::random(12);

                User::create([
                    'name' => $data['name'] ?? 'User',
                    'email' => $email,
                    'phone' => $data['phone'] ?? null,
                    'password' => bcrypt($randomPassword),
                    'is_active' => $data['is_active'] ?? true,
                ]);
                return 'created';
            }
        }

        return null;
    }

    private function importProduct($data, $action)
    {
        $name = $data['name'] ?? null;
        if (!$name) return null;

        $slug = $data['slug'] ?? Str::slug($name);

        $query = Product::where('slug', $slug);

        if ($action === 'update' || $action === 'both') {
            $product = $query->first();
            if ($product) {
                $categoryId = $product->category_id;
                if (!empty($data['category_id'])) {
                    $categoryId = $data['category_id'];
                }

                $product->update([
                    'name' => $data['name'] ?? $product->name,
                    'sku' => $data['sku'] ?? $product->sku,
                    'category_id' => $categoryId,
                    'brand_id' => $data['brand_id'] ?? $product->brand_id,
                    'price' => $data['price'] ?? $product->price,
                    'purchase_price' => $data['purchase_price'] ?? $product->purchase_price,
                    'discount_price' => $data['discount_price'] ?? $product->discount_price,
                    'quantity' => $data['quantity'] ?? $product->quantity,
                    'description' => $data['description'] ?? $product->description,
                    'short_description' => $data['short_description'] ?? $product->short_description,
                    'featured_image' => $data['featured_image'] ?? $product->featured_image,
                    'gallery_images' => $data['gallery_images'] ?? $product->gallery_images,
                    'is_featured' => isset($data['is_featured']) ? ($data['is_featured'] === '1' || $data['is_featured'] === 'true') : $product->is_featured,
                    'is_active' => isset($data['is_active']) ? ($data['is_active'] === '1' || $data['is_active'] === 'true') : $product->is_active,
                    'is_digital' => isset($data['is_digital']) ? ($data['is_digital'] === '1' || $data['is_digital'] === 'true') : $product->is_digital,
                    'digital_file' => $data['digital_file'] ?? $product->digital_file,
                    'meta_title' => $data['meta_title'] ?? $product->meta_title,
                    'meta_description' => $data['meta_description'] ?? $product->meta_description,
                ]);
                return 'updated';
            }
        }

        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                $categoryId = null;
                if (!empty($data['category_id'])) {
                    $categoryId = $data['category_id'];
                }

                Product::create([
                    'name' => $name,
                    'slug' => $slug,
                    'sku' => $data['sku'] ?? null,
                    'category_id' => $categoryId,
                    'brand_id' => $data['brand_id'] ?? null,
                    'price' => $data['price'] ?? 0,
                    'purchase_price' => $data['purchase_price'] ?? 0,
                    'discount_price' => $data['discount_price'] ?? 0,
                    'quantity' => $data['quantity'] ?? 0,
                    'description' => $data['description'] ?? null,
                    'short_description' => $data['short_description'] ?? null,
                    'featured_image' => $data['featured_image'] ?? null,
                    'gallery_images' => $data['gallery_images'] ?? null,
                    'is_featured' => isset($data['is_featured']) ? ($data['is_featured'] === '1' || $data['is_featured'] === 'true') : false,
                    'is_active' => isset($data['is_active']) ? ($data['is_active'] === '1' || $data['is_active'] === 'true') : true,
                    'is_digital' => isset($data['is_digital']) ? ($data['is_digital'] === '1' || $data['is_digital'] === 'true') : false,
                    'digital_file' => $data['digital_file'] ?? null,
                    'meta_title' => $data['meta_title'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                ]);
                return 'created';
            }
        }

        return null;
    }

    private function importBlog($data, $action)
    {
        $title = $data['title'] ?? null;
        if (!$title) return null;

        $slug = $data['slug'] ?? Str::slug($title);

        $query = Blog::where('slug', $slug);

        if ($action === 'update' || $action === 'both') {
            $blog = $query->first();
            if ($blog) {
                $blog->update([
                    'title' => $data['title'] ?? $blog->title,
                    'category_id' => $data['category_id'] ?? $blog->category_id,
                    'author_id' => $data['author_id'] ?? $blog->author_id,
                    'featured_image' => $data['featured_image'] ?? $blog->featured_image,
                    'short_description' => $data['short_description'] ?? $blog->short_description,
                    'content' => $data['content'] ?? $blog->content,
                    'meta_title' => $data['meta_title'] ?? $blog->meta_title,
                    'meta_description' => $data['meta_description'] ?? $blog->meta_description,
                    'is_published' => isset($data['is_published']) ? ($data['is_published'] === '1' || $data['is_published'] === 'true') : $blog->is_published,
                    'published_at' => $data['published_at'] ?? $blog->published_at,
                ]);
                return 'updated';
            }
        }

        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                Blog::create([
                    'title' => $title,
                    'slug' => $slug,
                    'category_id' => $data['category_id'] ?? null,
                    'author_id' => $data['author_id'] ?? 1,
                    'featured_image' => $data['featured_image'] ?? null,
                    'short_description' => $data['short_description'] ?? null,
                    'content' => $data['content'] ?? null,
                    'meta_title' => $data['meta_title'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'is_published' => isset($data['is_published']) ? ($data['is_published'] === '1' || $data['is_published'] === 'true') : false,
                    'published_at' => $data['published_at'] ?? now(),
                ]);
                return 'created';
            }
        }

        return null;
    }

    private function importProductBundle($data, $action)
    {
        $title = $data['title'] ?? null;
        if (!$title) return null;

        $slug = $data['slug'] ?? Str::slug($title);

        $query = ProductBundle::where('slug', $slug);

        if ($action === 'update' || $action === 'both') {
            $bundle = $query->first();
            if ($bundle) {
                $bundle->update([
                    'title' => $data['title'] ?? $bundle->title,
                    'sku' => $data['sku'] ?? $bundle->sku,
                    'discount_type' => $data['discount_type'] ?? $bundle->discount_type,
                    'discount_value' => $data['discount_value'] ?? $bundle->discount_value,
                    'start_date' => $data['start_date'] ?? $bundle->start_date,
                    'end_date' => $data['end_date'] ?? $bundle->end_date,
                    'is_featured' => isset($data['is_featured']) ? ($data['is_featured'] === '1' || $data['is_featured'] === 'true') : $bundle->is_featured,
                    'is_active' => isset($data['is_active']) ? ($data['is_active'] === '1' || $data['is_active'] === 'true') : $bundle->is_active,
                ]);
                return 'updated';
            }
        }

        if ($action === 'create' || $action === 'both') {
            if (!$query->exists()) {
                ProductBundle::create([
                    'title' => $title,
                    'slug' => $slug,
                    'sku' => $data['sku'] ?? null,
                    'discount_type' => $data['discount_type'] ?? 'percent',
                    'discount_value' => $data['discount_value'] ?? 0,
                    'start_date' => $data['start_date'] ?? now(),
                    'end_date' => $data['end_date'] ?? now()->addDays(30),
                    'is_featured' => isset($data['is_featured']) ? ($data['is_featured'] === '1' || $data['is_featured'] === 'true') : false,
                    'is_active' => isset($data['is_active']) ? ($data['is_active'] === '1' || $data['is_active'] === 'true') : true,
                ]);
                return 'created';
            }
        }

        return null;
    }

    /**
     * Download sample CSV template.
     */
    public function downloadTemplate(Request $request)
    {
        $type = $request->type;
        
        $template = $this->getTemplateData($type);
        
        if (empty($template)) {
            return back()->with('error', 'No template available for this type.');
        }

        $filename = $type . '-template-' . date('Y-m-d') . '.csv';

        return response()->stream(function () use ($template) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, array_keys($template[0]));
            
            // Sample data
            foreach ($template as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get template data for CSV download.
     */
    private function getTemplateData($type)
    {
        switch ($type) {
            case 'categories':
                return [
                    ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic devices and accessories', 'meta_title' => 'Electronics', 'meta_description' => 'Buy electronics online', 'is_featured' => '1', 'status' => 'active'],
                ];

            case 'brands':
                return [
                    ['name' => 'Apple', 'slug' => 'apple', 'meta_title' => 'Apple Products', 'meta_description' => 'Apple brand products', 'is_featured' => '1', 'status' => 'active'],
                ];

            case 'attributes':
                return [
                    ['name' => 'Size', 'slug' => 'size', 'status' => 'active'],
                ];

            case 'colors':
                return [
                    ['name' => 'Red', 'code' => '#FF0000', 'status' => 'active'],
                ];

            case 'coupons':
                return [
                    ['code' => 'SUMMER20', 'type' => 'percent', 'value' => '20', 'min_buy' => '100', 'max_discount' => '50', 'start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d', strtotime('+30 days')), 'is_first_order' => '0', 'is_public' => '1', 'is_active' => '1'],
                ];

            case 'banners':
                return [
                    ['title' => 'Summer Sale', 'url' => '/products', 'image' => 'banners/summer.jpg', 'position' => '1', 'is_published' => '1'],
                ];

            case 'sliders':
                return [
                    ['title' => 'Welcome', 'subtitle' => 'Welcome to our store', 'text' => 'Best products at best prices', 'image' => 'sliders/slide1.jpg', 'link' => '/products', 'link_text' => 'Shop Now', 'is_active' => '1', 'order' => '1'],
                ];

            case 'faqs':
                return [
                    ['question' => 'What is your return policy?', 'answer' => 'We offer 30-day returns on all products.', 'category' => 'general', 'is_active' => '1'],
                ];

            case 'pages':
                return [
                    ['title' => 'About Us', 'slug' => 'about-us', 'content' => 'About us content goes here.', 'meta_title' => 'About Us', 'meta_description' => 'Learn more about us', 'is_published' => '1'],
                ];

            case 'taxes':
                return [
                    ['name' => 'VAT', 'rate' => '15', 'is_active' => '1'],
                ];

            case 'delivery_zones':
                return [
                    ['name' => ' Dhaka', 'cost' => '50', 'free_shipping' => '0', 'min_shipping_amount' => '500', 'is_active' => '1'],
                ];

            case 'warehouses':
                return [
                    ['name' => 'Main Warehouse', 'address' => '123 Main St, City', 'phone' => '1234567890', 'is_default' => '1', 'is_active' => '1'],
                ];

            case 'subscribers':
                return [
                    ['email' => 'example@example.com', 'is_active' => '1'],
                ];

            case 'products':
                return [
                    ['name' => 'Sample Product', 'slug' => 'sample-product', 'sku' => 'SKU001', 'category_id' => '1', 'brand_id' => '1', 'price' => '99.99', 'purchase_price' => '50', 'discount_price' => '79.99', 'quantity' => '100', 'description' => 'Product description', 'short_description' => 'Short description', 'featured_image' => 'products/image.jpg', 'is_featured' => '1', 'is_active' => '1', 'is_digital' => '0'],
                ];

            case 'blogs':
                return [
                    ['title' => 'Sample Blog Post', 'slug' => 'sample-blog-post', 'category_id' => '1', 'featured_image' => 'blogs/blog1.jpg', 'short_description' => 'Blog summary', 'content' => 'Full blog content here', 'meta_title' => 'Blog Title', 'meta_description' => 'Blog description', 'is_published' => '1'],
                ];

            case 'product_bundles':
                return [
                    ['title' => 'Summer Bundle', 'slug' => 'summer-bundle', 'sku' => 'BUNDLE001', 'discount_type' => 'percent', 'discount_value' => '15', 'start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d', strtotime('+30 days')), 'is_featured' => '1', 'is_active' => '1'],
                ];

            default:
                return [];
        }
    }
}
