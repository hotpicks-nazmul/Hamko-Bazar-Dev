<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\LoginCode;
use App\Models\ActivityLog;
use App\Notifications\AdminLoginCode;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && in_array(Auth::user()->role, ['admin', 'staff'])) {
            return redirect()->route(Auth::user()->getFirstAllowedRoute());
        }
        
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Only allow admin/super_admin roles (not regular customers)
            if (!in_array($user->role, ['admin', 'super_admin'])) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'You do not have admin access.',
                ])->onlyInput('email');
            }

            // Check if admin user is active
            if (in_array($user->role, ['admin']) && $user->status !== 'active') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account is not active. Please contact administrator.',
                ])->onlyInput('email');
            }

            // Check if 2FA is enabled
            $twoFactorEnabled = \Illuminate\Support\Facades\DB::table('settings')
                ->where('key', 'two_factor_auth')
                ->value('value');

            if ($twoFactorEnabled === '1') {
                // Generate 2FA code and require verification
                $code = LoginCode::generateFor($user->email);

                try {
                    $user->notify(new AdminLoginCode($code));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Admin 2FA email failed: ' . $e->getMessage());
                }

                // Store user ID in session for 2FA verification
                $request->session()->put('2fa_user_id', $user->id);
                $request->session()->put('2fa_email', $user->email);

                // Log out until 2FA is verified
                Auth::logout();

                return redirect()->route('admin.verify-2fa');
            }

            // 2FA disabled — log in directly
            return redirect()->intended(route($user->getFirstAllowedRoute()));

        }

        // Log failed attempt
        \Illuminate\Support\Facades\Log::warning('Failed admin login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showVerifyForm(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.verify-2fa', [
            'email' => $request->session()->get('2fa_email'),
        ]);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = $request->session()->get('2fa_user_id');
        $email = $request->session()->get('2fa_email');

        if (!$userId || !$email) {
            return redirect()->route('admin.login')
                ->withErrors(['code' => 'Session expired. Please login again.']);
        }

        if (!LoginCode::verify($email, $request->code)) {
            return back()->withErrors([
                'code' => 'Invalid or expired verification code.',
            ]);
        }

        // Code verified — log them in
        $user = User::find($userId);
        if (!$user || !in_array($user->role, ['admin', 'super_admin'])) {
            $request->session()->forget(['2fa_user_id', '2fa_email']);
            return redirect()->route('admin.login');
        }

        Auth::login($user);
        $request->session()->regenerate();

        // Log successful login
        ActivityLog::adminLog(
            'Admin user logged in (2FA verified)',
            $user,
            $user,
            ['email' => $user->email, 'role' => $user->role]
        );

        $request->session()->forget(['2fa_user_id', '2fa_email']);

        return redirect()->intended(route($user->getFirstAllowedRoute()));
    }

    public function resendCode(Request $request)
    {
        $email = $request->session()->get('2fa_email');

        if (!$email) {
            return redirect()->route('admin.login');
        }

        $code = LoginCode::generateFor($email);
        $user = User::where('email', $email)->first();

        if ($user) {
            try {
                $user->notify(new AdminLoginCode($code));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('2FA resend failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'A new verification code has been sent to your email.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        
        if ($user) {
            ActivityLog::adminLog(
                'Admin user logged out',
                $user,
                $user,
                ['email' => $user->email, 'role' => $user->role]
            );
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login');
    }
}
