<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'alt',
        'thumbnail',
        'link',
        'button_text',
        'button_color',
        'button_text_color',
        'button_icon',
        'button_icon_color',
        'show_title',
        'show_subtitle',
        'show_button',
        'show_gradient',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_title' => 'boolean',
        'show_subtitle' => 'boolean',
        'show_button' => 'boolean',
        'show_gradient' => 'boolean',
    ];
}
