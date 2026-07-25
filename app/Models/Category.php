<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'thumbnail',
        'icon',
        'theme_type',
        'status',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_featured',
        'show_in_menu',
        'show_in_homepage',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'show_in_menu' => 'boolean',
        'show_in_homepage' => 'boolean',
    ];

    /**
     * Parent category relationship.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Children categories relationship.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    /**
     * All descendants (recursive).
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Products relationship.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Active products relationship.
     */
    public function activeProducts()
    {
        return $this->hasMany(Product::class)->where('is_active', true);
    }

    /**
     * Blogs relationship.
     */
    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    /**
     * Scope for active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive categories.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope for parent categories.
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for ordered categories.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope for featured categories.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for menu categories.
     */
    public function scopeForMenu($query)
    {
        return $query->where('show_in_menu', true);
    }

    /**
     * Scope for homepage categories.
     */
    public function scopeForHomepage($query)
    {
        return $query->where('show_in_homepage', true);
    }

    /**
     * Get products count attribute.
     */
    public function getProductsCountAttribute()
    {
        return $this->products()->where('is_active', true)->count();
    }

    /**
     * Get total products count (including children).
     */
    public function getTotalProductsCountAttribute()
    {
        $count = $this->products()->where('is_active', true)->count();
        
        foreach ($this->children as $child) {
            $count += $child->total_products_count;
        }
        
        return $count;
    }

    /**
     * Get all category IDs including self and descendants.
     */
    public function getAllChildrenIds()
    {
        $ids = [$this->id];
        
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }
        
        return $ids;
    }

    /**
     * Check if category has children.
     */
    public function hasChildren()
    {
        return $this->children()->count() > 0;
    }

    /**
     * Check if category has products.
     */
    public function hasProducts()
    {
        return $this->products()->count() > 0;
    }

    /**
     * Check if category can be deleted.
     */
    public function canBeDeleted()
    {
        return !$this->hasChildren() && !$this->hasProducts();
    }

    /**
     * Get depth level of category.
     */
    public function getDepthAttribute()
    {
        $depth = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        
        return $depth;
    }

    /**
     * Get breadcrumb path.
     */
    public function getBreadcrumbAttribute()
    {
        $breadcrumb = [$this];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($breadcrumb, $parent);
            $parent = $parent->parent;
        }
        
        return $breadcrumb;
    }

    /**
     * Get hierarchical name with dashes.
     */
    public function getHierarchicalNameAttribute()
    {
        $name = $this->name;
        $parent = $this->parent;
        
        while ($parent) {
            $name = $parent->name . ' → ' . $name;
            $parent = $parent->parent;
        }
        
        return $name;
    }

    /**
     * Auto-generate slug from name.
     */
    public static function boot()
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
            
            // Set default sort order
            if (empty($category->sort_order)) {
                $category->sort_order = static::max('sort_order') + 1;
            }
        });
        
        static::updating(function ($category) {
            if ($category->isDirty('name') && !$category->isDirty('slug')) {
                $category->slug = Str::slug($category->name);
                
                // Ensure unique slug
                $count = static::where('slug', 'like', $category->slug . '%')
                    ->where('id', '!=', $category->id)
                    ->count();
                if ($count > 0) {
                    $category->slug .= '-' . ($count + 1);
                }
            }
        });
    }

    /**
     * Get category tree as array.
     */
    public static function getTree()
    {
        return static::with('descendants')
            ->parents()
            ->ordered()
            ->get();
    }

    /**
     * Get flattened tree for select options.
     */
    public static function getFlattenedTree($excludeId = null)
    {
        $categories = static::with('descendants')
            ->parents()
            ->ordered()
            ->get();
        
        $flattened = [];
        
        $flatten = function ($categories, $depth = 0) use (&$flatten, &$flattened, $excludeId) {
            foreach ($categories as $category) {
                if ($excludeId && $category->id == $excludeId) {
                    continue;
                }
                
                $prefix = str_repeat('— ', $depth);
                $flattened[$category->id] = $prefix . $category->name;
                
                if ($category->children->count() > 0) {
                    $flatten($category->children, $depth + 1);
                }
            }
        };
        
        $flatten($categories);
        
        return $flattened;
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
        
        if (str_starts_with($this->image, '/storage/')) {
            return $this->image;
        }
        
        return '/storage/' . $this->image;
    }

    /**
     * Get status badge HTML.
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => '<span class="badge bg-success">Active</span>',
            'inactive' => '<span class="badge bg-secondary">Inactive</span>',
        ];
        
        return $badges[$this->status] ?? '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>';
    }
}
