<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class CheckInstallation
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->routeIs('install.*') || $request->is('install/*') || $request->is('install')) {
            if (File::exists(storage_path('framework/install.lock'))) {
                return redirect()->route('home');
            }
            return $next($request);
        }

        if (File::exists(storage_path('framework/install.lock'))) {
            return $next($request);
        }

        if (File::exists(base_path('.env'))) {
            try {
                DB::connection()->getPdo();
                if (Schema::hasTable('users')) {
                    return $next($request);
                }
            } catch (\Exception $e) {}
        }
        return redirect()->route('install.welcome');
    }
}
