<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;

class CheckGranularPermission
{
    private array $actionMap = [
        'index'   => 'view',
        'show'    => 'view',
        'list'    => 'view',
        'create'  => 'create',
        'store'   => 'create',
        'add'     => 'create',
        'new'     => 'create',
        'edit'    => 'edit',
        'update'  => 'edit',
        'destroy' => 'delete',
        'delete'  => 'delete',
        'remove'  => 'delete',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        if ($user->role === 'super_admin' || $user->is_super_admin) {
            return $next($request);
        }

        if (!in_array($user->role, ['admin', 'staff'])) {
            abort(403, 'Unauthorized access.');
        }

        $routeName = $request->route()?->getName();
        if (!$routeName || !str_starts_with($routeName, 'admin.')) {
            return $next($request);
        }

        $relativeName = substr($routeName, 6);
        $parts = explode('.', $relativeName);
        $module = $parts[0];

        if (count($parts) === 2) {
            $action = $parts[1];
            $permAction = $this->actionMap[$action] ?? $action;
            $granularKey = $module . '.' . $permAction;

            if (Permission::where('name', $granularKey)->where('guard_name', 'web')->exists()) {
                if (!$user->hasPermission($granularKey)) {
                    abort(403, 'You do not have permission to perform this action.');
                }
            }
        }

        if (count($parts) >= 3) {
            $subModule = $parts[1];
            $action = end($parts);
            $permAction = $this->actionMap[$action] ?? $action;
            $granularKey = $module . '.' . $subModule;

            if (Permission::where('name', $granularKey)->where('guard_name', 'web')->exists()) {
                if (!$user->hasPermission($granularKey)) {
                    $subGranularKey = $module . '.' . $subModule . '.' . $permAction;
                    if (Permission::where('name', $subGranularKey)->where('guard_name', 'web')->exists()) {
                        if (!$user->hasPermission($subGranularKey)) {
                            abort(403, 'You do not have permission to perform this action.');
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
