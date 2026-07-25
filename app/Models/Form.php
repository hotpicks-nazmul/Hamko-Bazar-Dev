<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'title',
        'success_message',
        'submit_button_text',
        'redirect_url',
        'is_active',
        'show_on_frontend',
        'submissions_count',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_on_frontend' => 'boolean',
        'submissions_count' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($form) {
            if (empty($form->slug)) {
                $form->slug = Str::slug($form->name);
            }
            
            // Ensure unique slug
            $originalSlug = $form->slug;
            $counter = 1;
            while (static::where('slug', $form->slug)->exists()) {
                $form->slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        });
    }

    /**
     * Get the form fields
     */
    public function fields()
    {
        return $this->hasMany(FormField::class, 'form_id')->orderBy('order');
    }

    /**
     * Get the form submissions
     */
    public function submissions()
    {
        return $this->hasMany(FormSubmission::class, 'form_id')->latest();
    }

    /**
     * Get active fields only
     */
    public function activeFields()
    {
        return $this->hasMany(FormField::class, 'form_id')
            ->where('is_visible', true)
            ->orderBy('order');
    }

    /**
     * Scope for active forms
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for frontend forms
     */
    public function scopeFrontend($query)
    {
        return $query->where('show_on_frontend', true)->where('is_active', true);
    }

    /**
     * Get the form URL for frontend
     */
    public function getUrlAttribute()
    {
        return route('forms.show', $this->slug);
    }

    /**
     * Increment submissions count
     */
    public function incrementSubmissions()
    {
        $this->increment('submissions_count');
    }

    /**
     * Get available field types
     */
    public static function getFieldTypes()
    {
        return [
            'text' => 'Text Input',
            'textarea' => 'Text Area',
            'email' => 'Email',
            'phone' => 'Phone Number',
            'number' => 'Number',
            'select' => 'Dropdown Select',
            'radio' => 'Radio Buttons',
            'checkbox' => 'Checkboxes',
            'date' => 'Date',
            'time' => 'Time',
            'datetime' => 'Date & Time',
            'file' => 'File Upload',
            'hidden' => 'Hidden Field',
            'password' => 'Password',
            'url' => 'URL',
            'color' => 'Color Picker',
            'range' => 'Range Slider',
            'tel' => 'Telephone',
        ];
    }
}
