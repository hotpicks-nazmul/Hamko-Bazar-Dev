<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Affiliate Model
 * 
 * TODO: Implement full functionality
 */
class Affiliate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'affiliate_code',
        'commission_rate',
        'balance',
        'total_earnings',
        'pending_balance',
        'payment_method',
        'payment_details',
        'website',
        'social_links',
        'status',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'commission_rate' => 'decimal:2',
        'balance' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'social_links' => 'array',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user that owns the affiliate.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the links for the affiliate.
     */
    public function links()
    {
        return $this->hasMany(AffiliateLink::class);
    }

    /**
     * Get the clicks for the affiliate.
     */
    public function clicks()
    {
        return $this->hasMany(AffiliateClick::class);
    }

    /**
     * Get the sales for the affiliate.
     */
    public function sales()
    {
        return $this->hasMany(AffiliateSale::class);
    }

    /**
     * Get the withdrawals for the affiliate.
     */
    public function withdrawals()
    {
        return $this->hasMany(AffiliateWithdrawal::class);
    }

    /**
     * Get approved affiliates.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get pending affiliates.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Check if affiliate is active.
     */
    public function isActive()
    {
        return $this->status === 'approved';
    }

    /**
     * Generate unique affiliate code.
     */
    public static function generateCode()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('affiliate_code', $code)->exists());

        return $code;
    }

    /**
     * Auto-generate affiliate code.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($affiliate) {
            if (empty($affiliate->affiliate_code)) {
                $affiliate->affiliate_code = self::generateCode();
            }
        });
    }
}
