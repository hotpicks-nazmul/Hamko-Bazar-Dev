<?php

return [

    'models' => [
        App\Models\Product::class,
        App\Models\Category::class,
    ],

    'cache' => [
        'key' => 'laravel-sitemap.' . config('app.url'),
        'lifetime' => 60 * 24 * 7, // 7 days
    ],

];
