<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * Uses Spatie's permission system with Granular fallback.
     *
     * Permission format: 'products' (legacy module-level) or 'products.view' (granular CRUD).
     * Staff must have either the exact permission OR the module-level permission.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        // Super admin has all permissions
        if ($user->role === 'super_admin' || $user->is_super_admin) {
            return $next($request);
        }

        // Admin and staff - must have the specific permission
        if (in_array($user->role, ['admin', 'staff'])) {
            $hasPerm = $user->hasPermission($permission);
            \Illuminate\Support\Facades\Log::debug('CheckPermission', [
                'user_id' => $user->id,
                'role' => $user->role,
                'permission' => $permission,
                'hasPermission' => $hasPerm,
                'url' => $request->fullUrl(),
            ]);
            if ($hasPerm) {
                return $next($request);
            }
            abort(403, 'You do not have permission to access this section.');
        }

        abort(403, 'Unauthorized access.');
    }
}
