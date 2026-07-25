<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Leaving the "log in as customer" mode.
 *
 * CustomerController::loginAs() stores the admin's id in the session and signs the admin in as the
 * customer. Without this method there was no way back: the admin session had been replaced, and the
 * only route back into the panel was logging in again by hand.
 */
class ImpersonationController extends Controller
{
    public function stop(Request $request)
    {
        $adminId = $request->session()->get('impersonator_id');
        $customerId = $request->session()->get('impersonating_customer_id');

        if (! $adminId) {
            return redirect()->route('home')->with('error', 'You are not browsing as another user.');
        }

        $admin = User::find($adminId);

        if (! $admin || ! in_array($admin->role, ['admin', 'super_admin', 'staff'], true)) {
            $request->session()->forget(['impersonator_id', 'impersonating_customer_id']);
            auth()->logout();

            return redirect()->route('login')->with('error', 'Your admin account is no longer available. Please sign in again.');
        }

        $customer = $customerId ? User::find($customerId) : null;

        ActivityLog::adminLog('Stopped browsing as customer', $customer, $admin, [
            'customer_id' => $customerId,
            'customer_name' => $customer?->name,
        ]);

        $request->session()->forget(['impersonator_id', 'impersonating_customer_id']);

        auth()->login($admin);
        $request->session()->regenerate();

        return $customer
            ? redirect()->route('admin.customers.show', $customer->id)->with('success', 'You are back in your own account.')
            : redirect()->route('admin.customers.index')->with('success', 'You are back in your own account.');
    }
}
