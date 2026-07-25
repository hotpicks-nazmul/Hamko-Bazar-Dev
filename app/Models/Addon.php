<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Addon extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'author',
        'author_website',
        'website',
        'status',
        'is_core',
        'sort_order',
        'icon',
        'settings',
        'installed_at',
    ];

    protected $casts = [
        'is_core' => 'boolean',
        'settings' => 'array',
        'installed_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($addon) {
            if (empty($addon->slug)) {
                $addon->slug = Str::slug($addon->name);
            }
            if (empty($addon->installed_at)) {
                $addon->installed_at = now();
            }
        });
    }

    /**
     * Scope to get only active addons.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get only installed addons.
     */
    public function scopeInstalled($query)
    {
        return $query->where('status', '!=', 'uninstalled');
    }

    /**
     * Check if the addon is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the addon is installed.
     */
    public function isInstalled(): bool
    {
        return in_array($this->status, ['active', 'inactive']);
    }

    /**
     * Check if the addon is a core addon (cannot be uninstalled).
     */
    public function isCore(): bool
    {
        return $this->is_core === true;
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'warning',
            'uninstalled' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            'uninstalled' => 'Not Installed',
            default => 'Unknown',
        };
    }

    /**
     * Activate the addon.
     */
    public function activate(): bool
    {
        $this->status = 'active';
        return $this->save();
    }

    /**
     * Deactivate the addon.
     */
    public function deactivate(): bool
    {
        $this->status = 'inactive';
        return $this->save();
    }

    /**
     * Get the addon icon or default icon.
     */
    public function getIconAttribute($value): string
    {
        return $value ?? 'bi bi-puzzle';
    }
}
