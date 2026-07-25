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

        // Check warehouse_id for warehouse-specific routes
        if ($request->has('id') && $user->warehouse_id && (int) $request->id !== $user->warehouse_id) {
            abort(403, 'You can only access your assigned warehouse.');
        }

        return $next($request);
    }
}
