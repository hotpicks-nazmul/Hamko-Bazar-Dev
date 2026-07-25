<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DigitalCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'parent_id',
        'order',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Parent category relationship
     */
    public function parent()
    {
        return $this->belongsTo(DigitalCategory::class, 'parent_id');
    }

    /**
     * Child categories relationship
     */
    public function children()
    {
        return $this->hasMany(DigitalCategory::class, 'parent_id')->orderBy('order');
    }

    /**
     * Products relationship
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'digital_category_id');
    }

    /**
     * Active products relationship
     */
    public function activeProducts()
    {
        return $this->products()->where('is_active', true);
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for root categories (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope ordered
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /**
     * Get flattened tree for select dropdowns
     */
    public static function getFlattenedTree($parentId = null, $prefix = '')
    {
        $categories = static::where('parent_id', $parentId)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $result = [];

        foreach ($categories as $category) {
            $result[$category->id] = $prefix . $category->name;
            $result = $result + static::getFlattenedTree($category->id, $prefix . '— ');
        }

        return $result;
    }

    /**
     * Get hierarchical tree
     */
    public static function getTree()
    {
        return static::with('children.children')
            ->root()
            ->ordered()
            ->get();
    }

    /**
     * Auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
                
                // Ensure unique slug
                $count = static::where('slug', 'like', $category->slug . '%')->count();
                if ($count > 0) {
                    $category->slug .= '-' . ($count + 1);
                }
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get product count
     */
    public function getProductCountAttribute()
    {
        return $this->products()->count();
    }

    /**
     * Get image URL attribute.
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return null;
        }
        
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }
        
        $path = ltrim($this->image, '/');
        
        return '/storage/' . $path;
    }

    /**
     * Get all descendant IDs
     */
    public function getDescendantIds()
    {
        $ids = [$this->id];
        
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getDescendantIds());
        }

        return $ids;
    }

    /**
     * Check if has children
     */
    public function hasChildren()
    {
        return $this->children()->exists();
    }

    /**
     * Get breadcrumb path
     */
    public function getBreadcrumb()
    {
        $breadcrumb = [$this];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($breadcrumb, $parent);
            $parent = $parent->parent;
        }

        return $breadcrumb;
    }
}
