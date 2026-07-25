<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url',
        'target',
        'icon',
        'css_class',
        'type',
        'reference_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'reference_id' => 'integer',
    ];

    /**
     * Get the menu that owns this item.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Get the parent menu item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get the child menu items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get the full URL for this menu item.
     */
    public function getFullUrlAttribute()
    {
        if (empty($this->url)) {
            return '#';
        }

        // If it's already a full URL, return as is
        if (str_starts_with($this->url, 'http://') || str_starts_with($this->url, 'https://')) {
            return $this->url;
        }

        // Handle different types
        switch ($this->type) {
            case 'category':
                if ($this->reference_id) {
                    $category = Category::find($this->reference_id);
                    if ($category) {
                        return route('products.category', $category->slug);
                    }
                }
                return $this->url;

            case 'page':
                if ($this->reference_id) {
                    $page = Page::find($this->reference_id);
                    if ($page) {
                        return route('page', $page->slug);
                    }
                }
                return $this->url;

            case 'product':
                if ($this->reference_id) {
                    $product = Product::find($this->reference_id);
                    if ($product) {
                        return route('product.details', $product->slug);
                    }
                }
                return $this->url;

            default:
                // For custom links, prepend slash if needed
                return '/' . ltrim($this->url, '/');
        }
    }

    /**
     * Scope to get only active items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get root items (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Toggle item status.
     */
    public function toggleStatus(): void
    {
        $this->update(['is_active' => !$this->is_active]);
    }

    /**
     * Check if this item has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }
}
