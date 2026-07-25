<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'query',
        'user_id',
        'ip_address',
        'user_agent',
        'results_count',
        'is_autocomplete',
    ];

    protected $casts = [
        'is_autocomplete' => 'boolean',
        'results_count' => 'integer',
    ];

    /**
     * Get the user that performed the search.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get popular searches.
     */
    public function scopePopular($query, $limit = 10)
    {
        return $query->select('query', \DB::raw('COUNT(*) as search_count'))
            ->groupBy('query')
            ->orderByDesc('search_count')
            ->limit($limit);
    }

    /**
     * Scope to get recent searches.
     */
    public function scopeRecent($query, $limit = 50)
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by autocomplete searches.
     */
    public function scopeAutocomplete($query)
    {
        return $query->where('is_autocomplete', true);
    }

    /**
     * Scope to filter by manual searches.
     */
    public function scopeManual($query)
    {
        return $query->where('is_autocomplete', false);
    }
}
