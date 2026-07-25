<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the menu items for this menu.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')->orderBy('sort_order');
    }

    /**
     * Get only active menu items with nested structure.
     */
    public function activeItems()
    {
        return $this->hasMany(MenuItem::class, 'menu_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * Get the menu items as a tree (nested structure).
     */
    public function getTreeItemsAttribute()
    {
        return $this->items()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Scope to get only active menus.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Toggle menu status.
     */
    public function toggleStatus(): void
    {
        $this->update(['is_active' => !$this->is_active]);
    }
}
