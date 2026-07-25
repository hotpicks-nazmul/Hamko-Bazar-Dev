<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('staff.login');
        }

        $user = Auth::user();
        
        // Check if user is staff
        if ($user->role !== 'staff') {
            Auth::logout();
            return redirect()->route('staff.login')->withErrors([
                'email' => 'You do not have staff access.',
            ]);
        }

        // Check if staff is active
        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('staff.login')->withErrors([
                'email' => 'Your account is not active. Please contact administrator.',
            ]);
        }

        // Warehouse scoping. `$request->has('id')` reads the *input* bag, and a route parameter is not
        // input — so this check never ran and any staff account could open, pick and pack another
        // warehouse's queue. Read the route parameter, and refuse a staff account that has no warehouse
        // assigned at all (the old `$user->warehouse_id &&` guard skipped the check for exactly those).
        $routeWarehouse = $request->route('id');

        if ($routeWarehouse !== null) {
            if (! $user->warehouse_id) {
                abort(403, 'No warehouse is assigned to your account. Please contact the administrator.');
            }

            if ((int) $routeWarehouse !== (int) $user->warehouse_id) {
                abort(403, 'You can only access your assigned warehouse.');
            }
        }

        return $next($request);
    }
}
