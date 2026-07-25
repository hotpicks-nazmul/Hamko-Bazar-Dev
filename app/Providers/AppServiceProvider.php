<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS in production only
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        // Prevent lazy loading in non-production environments (except local for debugging)
        // Model::preventLazyLoading(!app()->isProduction());
    }
}
