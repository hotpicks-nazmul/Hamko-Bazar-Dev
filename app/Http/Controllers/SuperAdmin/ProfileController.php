<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\ActivityLog;

class ProfileController extends Controller
{
    public function show()
    {
        return view('super-admin.profile');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name' => 'required|string|max:255',
        ];

        // If email is changing, require current password confirmation
        if ($request->email !== $user->email) {
            $rules['email'] = 'required|string|email|max:255|unique:users,email,' . $user->id;
            $rules['current_password'] = 'required|string';
        } else {
            $rules['email'] = 'required|string|email|max:255';
        }

        $validated = $request->validate($rules);

        // Verify current password if email is changing
        if (isset($validated['current_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors([
                    'current_password' => 'Current password is incorrect. Email not changed.',
                ])->withInput();
            }
        }

        $emailChanged = $request->email !== $user->email;

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        ActivityLog::adminLog(
            $emailChanged ? 'Super Admin updated profile (email changed)' : 'Super Admin updated profile',
            $user,
            $user,
            ['email' => $user->email, 'changes' => $emailChanged ? 'email,name' : 'name']
        );

        if ($emailChanged) {
            // Email changed — log out and require re-login with new email (2FA will trigger)
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('super-admin.login')
                ->with('success', 'Email changed successfully. Please login with your new email.');
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        ActivityLog::adminLog(
            'Super Admin changed password',
            $user,
            $user,
            ['email' => $user->email]
        );

        // Log out and require re-login
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super-admin.login')
            ->with('success', 'Password changed successfully. Please login with your new password.');
    }
}
