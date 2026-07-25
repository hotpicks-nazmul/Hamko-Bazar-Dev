<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Affiliate Click Model
 * 
 * TODO: Implement full functionality
 */
class AffiliateClick extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'affiliate_id',
        'link_id',
        'product_id',
        'ip_address',
        'user_agent',
        'referrer',
        'clicked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    /**
     * Get the affiliate that owns the click.
     */
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * Get the link that was clicked.
     */
    public function link()
    {
        return $this->belongsTo(AffiliateLink::class, 'link_id');
    }

    /**
     * Get the product that was clicked.
     */
    public function product()
    {
        return $this->belongsTo(AffiliateProduct::class, 'product_id');
    }

    /**
     * Get the sale from this click (if converted).
     */
    public function sale()
    {
        return $this->hasOne(AffiliateSale::class, 'click_id');
    }
}
