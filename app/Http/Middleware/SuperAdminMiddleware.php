<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('super-admin.login');
        }

        $user = Auth::user();
        
        // Check if user is super_admin
        if ($user->role !== 'super_admin') {
            Auth::logout();
            return redirect()->route('super-admin.login')->withErrors([
                'email' => 'You do not have super admin access.',
            ]);
        }

        return $next($request);
    }
}
