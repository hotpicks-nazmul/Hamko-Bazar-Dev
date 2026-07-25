<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function created(User $user)
    {
        if ($user->role === 'customer') {
            notify_new_customer($user);
        }

        if ($user->role === 'vendor' && $user->verification_status === 'pending') {
            notify_seller_request($user);
        }
    }

    public function updated(User $user)
    {
        if ($user->wasChanged('verification_status') && $user->verification_status === 'pending') {
            notify_seller_request($user);
        }
    }
}
