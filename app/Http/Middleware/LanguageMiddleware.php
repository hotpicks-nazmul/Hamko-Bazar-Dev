<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip DB queries during installation
        if (!$this->isAppInstalled()) {
            App::setLocale(config('app.locale', 'en'));
            view()->share('isRTL', false);
            view()->share('currentLanguage', null);
            return $next($request);
        }

        // Get locale from session or use default
        $locale = Session::get('locale');
        
        if (!$locale) {
            // Get default language from database
            $defaultLanguage = Language::getDefault();
            $locale = $defaultLanguage ? $defaultLanguage->code : 'en';
            Session::put('locale', $locale);
        }
        
        // Set application locale
        App::setLocale($locale);
        
        // Get current language for RTL detection
        $currentLanguage = Language::where('code', $locale)->first();
        
        if ($currentLanguage) {
            // Share RTL status with all views
            view()->share('isRTL', $currentLanguage->is_rtl);
            view()->share('currentLanguage', $currentLanguage);
        }
        
        return $next($request);
    }

    /**
     * Check if the application is installed (install.lock exists).
     */
    protected function isAppInstalled(): bool
    {
        try {
            return File::exists(storage_path('framework/install.lock'));
        } catch (\Exception $e) {
            return false;
        }
    }
}
