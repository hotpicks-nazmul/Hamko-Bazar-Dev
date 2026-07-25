<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubmenuPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        // Super admin bypass submenu checks
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if (!$routeName) {
            return $next($request);
        }

        $submenus = \App\Helpers\PermissionHelper::submenus();
        foreach ($submenus as $module => $items) {
            if (isset($items[$routeName])) {
                // Check global visibility (admin master override)
                if (!\App\Helpers\PermissionHelper::isSubmenuVisible($routeName)) {
                    abort(403, 'This section is currently disabled.');
                }

                // Check per-user permission
                $hasSubPerm = $user->hasPermission('submenu:' . $routeName);
                \Illuminate\Support\Facades\Log::debug('CheckSubmenuPermission', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'route' => $routeName,
                    'hasPermission' => $hasSubPerm,
                    'url' => $request->fullUrl(),
                ]);
                if (!$hasSubPerm) {
                    abort(403, 'You do not have permission to access this section.');
                }

                break;
            }
        }

        return $next($request);
    }
}
