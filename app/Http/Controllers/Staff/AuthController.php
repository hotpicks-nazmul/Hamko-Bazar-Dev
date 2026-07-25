<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ActivityLog;

class AuthController extends Controller
{
    /**
     * Show staff login form.
     */
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->role === 'staff') {
            return redirect()->route('staff.warehouses.picking.index', Auth::user()->warehouse_id ?? 1);
        }
        
        return view('staff.auth.login');
    }

    /**
     * Handle staff login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Only allow staff role
            if ($user->role !== 'staff') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'You do not have staff access.',
                ])->onlyInput('email');
            }

            // Check if user is active
            if ($user->status !== 'active') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account is not active. Please contact administrator.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            
            // Log staff login activity
            ActivityLog::adminLog(
                'Staff logged in',
                $user,
                $user,
                ['email' => $user->email, 'role' => $user->role, 'designation' => $user->designation]
            );
            
            // Redirect to their warehouse picking dashboard
            if ($user->warehouse_id) {
                return redirect()->route('staff.warehouses.picking.index', $user->warehouse_id);
            }
            
            // If no warehouse assigned, go to admin dashboard
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle staff logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log staff logout activity
        if ($user) {
            ActivityLog::adminLog(
                'Staff logged out',
                $user,
                $user,
                ['email' => $user->email, 'role' => $user->role]
            );
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('staff.login');
    }
}
