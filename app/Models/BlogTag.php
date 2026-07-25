<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    /**
     * Get the blogs with this tag.
     */
    public function blogs()
    {
        return $this->belongsToMany(Blog::class, 'blog_tag', 'blog_tag_id', 'blog_id');
    }

    /**
     * Get published blogs count.
     */
    public function getPublishedBlogsCountAttribute()
    {
        return $this->blogs()->where('status', 'published')->count();
    }

    /**
     * Get total blogs count.
     */
    public function getBlogsCountAttribute()
    {
        return $this->blogs()->count();
    }

    /**
     * Get status badge HTML.
     */
    public function getStatusBadgeAttribute()
    {
        if ($this->status === 'active') {
            return '<span class="badge bg-success">Active</span>';
        }
        return '<span class="badge bg-secondary">Inactive</span>';
    }

    /**
     * Scope to get only active tags.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
