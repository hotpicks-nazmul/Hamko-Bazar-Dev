<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductQA extends Model
{
    use HasFactory;

    protected $table = 'product_qa';

    protected $fillable = [
        'product_id',
        'user_id',
        'question',
        'answer',
        'answered_by',
        'answered_at',
        'status',
        'is_featured',
        'questioner_name',
        'questioner_email',
        'is_anonymous',
        'helpful_count',
        'not_helpful_count',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_anonymous' => 'boolean',
    ];

    /**
     * Get the product that the Q&A belongs to.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who asked the question.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who answered the question.
     */
    public function answerer()
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    /**
     * Scope to get pending questions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get answered questions.
     */
    public function scopeAnswered($query)
    {
        return $query->where('status', 'answered');
    }

    /**
     * Scope to get published questions.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope to get featured questions.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Check if the question is answered.
     */
    public function isAnswered()
    {
        return !is_null($this->answer);
    }

    /**
     * Check if the question is published.
     */
    public function isPublished()
    {
        return $this->status === 'published';
    }

    /**
     * Get the questioner display name.
     */
    public function getQuestionerNameAttribute()
    {
        if ($this->is_anonymous) {
            return 'Anonymous';
        }
        
        return $this->questioner_name ?? ($this->user ? $this->user->name : 'Guest');
    }

    /**
     * Get helpful percentage.
     */
    public function getHelpfulPercentageAttribute()
    {
        $total = $this->helpful_count + $this->not_helpful_count;
        
        if ($total === 0) {
            return 0;
        }
        
        return round(($this->helpful_count / $total) * 100);
    }
}