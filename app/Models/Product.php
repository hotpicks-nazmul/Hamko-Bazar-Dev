<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'digital_category_id',
        'brand_id',
        'parent_id',
        'name',
        'slug',
        'sku',
        'product_code',
        'barcode',
        'brand',
        'short_description',
        'long_description',
        'price',
        'sale_price',
        'discount_starts_at',
        'discount_ends_at',
        'cost_price',
        'purchase_price',
        'quantity',
        'low_stock_threshold',
        'stock_status',
        'stock_update_date',
        'weight',
        'dimensions',
        'specs',
        'images',
        'featured_image',
        'featured_thumbnail',
        'thumbnail',
        'gallery',
        'attributes',
        'variations',
        'colors',
        'tags',
        'is_featured',
        'is_active',
        'is_approved',
        'approved_at',
        'is_digital',
        'download_link',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'file_format',
        'download_limit',
        'download_expiry_days',
        'installation_instructions',
        'system_requirements',
        'version',
        'license_type',
        'additional_files',
        'requires_license_key',
        'auto_generate_license',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'created_by',
        'seller_id',
        'store_id',
        'product_source',
    ];

    protected $casts = [
        'images' => 'array',
        'gallery' => 'array',
        'attributes' => 'array',
        'variations' => 'array',
        'colors' => 'array',
        'tags' => 'array',
        'specs' => 'array',
        'additional_files' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_digital' => 'boolean',
        'is_approved' => 'boolean',
        'requires_license_key' => 'boolean',
        'auto_generate_license' => 'boolean',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'stock_update_date' => 'date',
        'approved_at' => 'datetime',
        'discount_starts_at' => 'datetime',
        'discount_ends_at' => 'datetime',
    ];

    /**
     * Get the category of the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the digital category of the product.
     */
    public function digitalCategory()
    {
        return $this->belongsTo(DigitalCategory::class, 'digital_category_id');
    }

    /**
     * Get the brand of the product.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the seller of the product (for seller products).
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the store of the product.
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the creator of the product.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the reviews for the product.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get approved reviews only.
     */
    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    /**
     * Get the license keys for digital product.
     */
    public function licenseKeys()
    {
        return $this->hasMany(LicenseKey::class);
    }

    /**
     * Get the digital downloads for this product.
     */
    public function digitalDownloads()
    {
        return $this->hasMany(DigitalDownload::class);
    }

    /**
     * Get available license keys.
     */
    public function availableLicenseKeys()
    {
        return $this->licenseKeys()->available();
    }

    /**
     * Get attribute values for this product.
     */
    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values')
            ->withPivot('attribute_id', 'price', 'quantity', 'image', 'sku', 'is_visible')
            ->withTimestamps();
    }

    /**
     * Get visible attribute values for this product.
     */
    public function visibleAttributeValues()
    {
        return $this->attributeValues()
            ->wherePivot('is_visible', true);
    }

    /**
     * Get colors for this product.
     */
    public function colors()
    {
        return $this->belongsToMany(Color::class, 'product_colors')
            ->withPivot(['image', 'quantity', 'price_adjustment', 'sku', 'is_visible'])
            ->withTimestamps();
    }

    /**
     * Get visible colors for this product.
     */
    public function visibleColors()
    {
        return $this->colors()
            ->wherePivot('is_visible', true);
    }

    /**
     * Get Q&A entries for this product.
     */
    public function qa()
    {
        return $this->hasMany(ProductQA::class, 'product_id');
    }

    /**
     * Get published Q&A entries for this product.
     */
    public function publishedQA()
    {
        return $this->qa()->where('status', 'published');
    }

    public function getFinalPriceAttribute()
    {
        return $this->isOnSale() ? $this->sale_price : $this->price;
    }

    /**
     * Calculate tax for this product
     */
    public function calculateTax($country = null, $state = null, $zipCode = null): float
    {
        return \App\Services\TaxHelper::calculateTax($this->final_price, $country, $state, $zipCode);
    }

    /**
     * Get tax rate for this product
     */
    public function getTaxRate($country = null, $state = null, $zipCode = null): float
    {
        return \App\Services\TaxHelper::getTaxRate($country, $state, $zipCode);
    }

    /**
     * Get price including tax
     */
    public function getPriceWithTax($country = null, $state = null, $zipCode = null): float
    {
        return $this->final_price + $this->calculateTax($country, $state, $zipCode);
    }

    /**
     * Get formatted tax amount
     */
    public function getFormattedTax($country = null, $state = null, $zipCode = null): string
    {
        return \App\Services\TaxHelper::formatTaxAmount($this->calculateTax($country, $state, $zipCode));
    }

    /**
     * Get formatted price with tax
     */
    public function getFormattedPriceWithTax($country = null, $state = null, $zipCode = null): string
    {
        return \App\Services\TaxHelper::formatTaxAmount($this->getPriceWithTax($country, $state, $zipCode));
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->isOnSale() && $this->price > 0) {
            return round((($this->price - $this->sale_price) / $this->price) * 100);
        }
        return 0;
    }

    /**
     * Get average rating from approved reviews only.
     */
    public function getAverageRatingAttribute()
    {
        return $this->approvedReviews()->avg('rating') ?? 0;
    }

    /**
     * Get approved reviews count.
     */
    public function getApprovedReviewsCountAttribute()
    {
        return $this->approvedReviews()->count();
    }

    /**
     * Get rating distribution for approved reviews.
     */
    public function getRatingDistributionAttribute()
    {
        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $distribution[$i] = $this->approvedReviews()->where('rating', $i)->count();
        }
        return $distribution;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope for in-house products (products without seller).
     */
    public function scopeInHouse($query)
    {
        return $query->where('products.product_source', 'in_house')
                     ->whereNull('products.seller_id');
    }

    /**
     * Scope for seller products.
     */
    public function scopeSellerProducts($query)
    {
        return $query->where('products.product_source', 'seller')
                     ->whereNotNull('products.seller_id');
    }

    /**
     * Scope for approved products.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope for low stock products.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'low_stock_threshold')
                     ->where('quantity', '>', 0);
    }

    /**
     * Scope for out of stock products.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    /**
     * Scope for digital products.
     */
    public function scopeDigital($query)
    {
        return $query->where('is_digital', true);
    }

    /**
     * Scope for physical products.
     */
    public function scopePhysical($query)
    {
        return $query->where('is_digital', false);
    }

    /**
     * Check if product is digital.
     */
    public function isDigital(): bool
    {
        return (bool) $this->is_digital;
    }

    /**
     * Check if product requires license key.
     */
    public function requiresLicenseKey(): bool
    {
        return $this->is_digital && $this->requires_license_key;
    }

    /**
     * Get file size formatted.
     */
    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Get available license keys count.
     */
    public function getAvailableLicenseKeysCountAttribute(): int
    {
        return $this->licenseKeys()->available()->count();
    }

    /**
     * Check if product is in-house.
     */
    public function isInHouse(): bool
    {
        return $this->product_source === 'in_house' && $this->seller_id === null;
    }

    /**
     * Check if product is from seller.
     */
    public function isSellerProduct(): bool
    {
        return $this->product_source === 'seller' && $this->seller_id !== null;
    }

    /**
     * Check if product is low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->low_stock_threshold && $this->quantity > 0;
    }

    /**
     * Check if product is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Get profit margin.
     */
    public function getProfitMarginAttribute(): float
    {
        $cost = $this->purchase_price ?? $this->cost_price ?? 0;
        $price = $this->sale_price ?? $this->price;
        
        if ($cost > 0 && $price > 0) {
            return round((($price - $cost) / $price) * 100, 2);
        }
        
        return 0;
    }

    /**
     * Get profit amount.
     */
    public function getProfitAmountAttribute(): float
    {
        $cost = $this->purchase_price ?? $this->cost_price ?? 0;
        $price = $this->sale_price ?? $this->price;
        
        return $price - $cost;
    }

    /**
     * Get stock value (quantity * cost price).
     */
    public function getStockValueAttribute(): float
    {
        $cost = $this->purchase_price ?? $this->cost_price ?? 0;
        return $this->quantity * $cost;
    }

    /**
     * Get retail value (quantity * selling price).
     */
    public function getRetailValueAttribute(): float
    {
        $price = $this->sale_price ?? $this->price;
        return $this->quantity * $price;
    }

    /**
     * Get the product's main image.
     */
    public function getImageAttribute()
    {
        return $this->featured_image ?? ($this->images[0] ?? null);
    }

    /**
     * Get the product's featured image URL.
     */
    public function getFeaturedImageUrlAttribute()
    {
        $image = $this->featured_image;
        if ($image) {
            if (str_starts_with($image, '/storage/')) {
                return asset($image);
            }
            if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
                return $image;
            }
            return asset('storage/' . $image);
        }
        return null;
    }

    /**
     * Get the product's thumbnail URL.
     */
    public function getThumbnailUrlAttribute()
    {
        $thumb = $this->featured_thumbnail ?? $this->thumbnail;
        if ($thumb) {
            if (str_starts_with($thumb, '/storage/')) {
                return asset($thumb);
            }
            if (str_starts_with($thumb, 'http://') || str_starts_with($thumb, 'https://')) {
                return $thumb;
            }
            return asset('storage/' . $thumb);
        }
        return $this->featured_image_url;
    }

    /**
     * Get reviews count.
     */
    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    /**
     * Get stock quantity.
     */
    public function getStockAttribute()
    {
        return $this->quantity ?? 0;
    }

    /**
     * Get discount percent.
     */
    public function getDiscountPercentAttribute()
    {
        if ($this->isOnSale() && $this->price > 0) {
            return round((($this->price - $this->sale_price) / $this->price) * 100);
        }
        return 0;
    }

    /**
     * Get sale price or regular price.
     */
    public function getSalePriceAttribute($value)
    {
        return $value;
    }

    /**
     * Check if product is on sale.
     */
    public function isOnSale()
    {
        // First check if there's a sale price
        if (!$this->sale_price || $this->sale_price >= $this->price) {
            return false;
        }
        
        // If no date restrictions, sale is active
        if (!$this->discount_starts_at && !$this->discount_ends_at) {
            return true;
        }
        
        $now = now();
        
        // Check if sale has started
        if ($this->discount_starts_at && $now->lt($this->discount_starts_at)) {
            return false;
        }
        
        // Check if sale has ended
        if ($this->discount_ends_at && $now->gt($this->discount_ends_at)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if discount is scheduled (not yet active).
     */
    public function isDiscountScheduled()
    {
        return $this->sale_price 
            && $this->discount_starts_at 
            && now()->lt($this->discount_starts_at);
    }
    
    /**
     * Check if discount has expired.
     */
    public function isDiscountExpired()
    {
        return $this->sale_price 
            && $this->discount_ends_at 
            && now()->gt($this->discount_ends_at);
    }
    
    /**
     * Check if discount is currently active (within date range).
     */
    public function isDiscountActive()
    {
        return $this->isOnSale();
    }

    /**
     * Get current price (sale price if on sale, otherwise regular price).
     */
    public function getCurrentPriceAttribute()
    {
        return $this->isOnSale() ? $this->sale_price : $this->price;
    }
    
    /**
     * Get the effective sale price (only if discount is currently active).
     */
    public function getEffectiveSalePriceAttribute()
    {
        return $this->isOnSale() ? $this->sale_price : null;
    }

    /**
     * Get review count.
     */
    public function getReviewCountAttribute()
    {
        return $this->reviews()->count();
    }

    /**
     * Get related products for this product.
     */
    public function relatedProducts()
    {
        return $this->belongsToMany(Product::class, 'related_products', 'product_id', 'related_product_id')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * Get products that have this product as related.
     */
    public function parentProducts()
    {
        return $this->belongsToMany(Product::class, 'related_products', 'related_product_id', 'product_id')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    /**
     * Get related products with product details.
     */
    public function getRelatedProductsAttribute()
    {
        return $this->relatedProducts()->where('is_active', true)->get();
    }

    /**
     * Get variants of this product.
     */
    public function variants()
    {
        return $this->hasMany(Product::class, 'parent_id');
    }

    /**
     * Check if this product is a variant.
     */
    public function isVariant()
    {
        return !empty($this->parent_id);
    }

    /**
     * Get the parent product.
     */
    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }
}
