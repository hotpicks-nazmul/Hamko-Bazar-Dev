<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class SeoRedirectMiddleware
{
    /**
     * Check if the application is installed.
     */
    protected function isAppInstalled(): bool
    {
        try {
            return File::exists(storage_path('framework/install.lock'));
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip DB queries during installation
        if (!$this->isAppInstalled()) {
            return $next($request);
        }

        // Skip for admin routes
        if ($request->is('admin/*')) {
            return $next($request);
        }

        // Skip for API routes
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Skip for install routes
        if ($request->routeIs('install.*') || $request->is('install/*')) {
            return $next($request);
        }

        // Get the current path
        $currentPath = $request->getPathInfo();

        // Get redirects from settings
        $redirects = json_decode(Setting::get('seo_redirects', '[]'), true);

        if (!empty($redirects)) {
            foreach ($redirects as $redirect) {
                $from = $redirect['from'] ?? '';
                $to = $redirect['to'] ?? '';
                $type = $redirect['type'] ?? 301;

                // Check if current path matches the redirect source
                if ($from && $currentPath === $from) {
                    // Build the redirect URL
                    $redirectUrl = $to;

                    // If the destination is not a full URL, prepend the base URL
                    if (!str_starts_with($redirectUrl, 'http')) {
                        $redirectUrl = url($to);
                    }

                    // Return redirect response
                    return redirect($redirectUrl, (int)$type);
                }
            }
        }

        return $next($request);
    }
}
