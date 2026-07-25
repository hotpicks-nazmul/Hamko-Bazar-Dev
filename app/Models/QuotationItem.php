<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'product_id',
        'product_name',
        'description',
        'variation',
        'quantity',
        'unit_price',
        'total',
        'sort_order',
    ];

    protected $casts = [
        'variation' => 'array',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'quantity' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the quotation that owns the item.
     */
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * Get the product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate the total for this item.
     */
    public function calculateTotal(): void
    {
        $this->total = $this->quantity * $this->unit_price;
        $this->save();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (!$item->total) {
                $item->total = $item->quantity * $item->unit_price;
            }
        });

        static::updating(function ($item) {
            $item->total = $item->quantity * $item->unit_price;
        });

        static::saved(function ($item) {
            // Recalculate quotation totals
            if ($item->quotation) {
                $item->quotation->calculateTotals();
            }
        });

        static::deleted(function ($item) {
            // Recalculate quotation totals
            if ($item->quotation) {
                $item->quotation->calculateTotals();
            }
        });
    }
}
