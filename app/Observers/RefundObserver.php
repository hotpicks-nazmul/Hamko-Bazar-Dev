<?php

namespace App\Observers;

use App\Models\Refund;

class RefundObserver
{
    public function created(Refund $refund)
    {
        if ($refund->status === 'pending') {
            notify_refund_request($refund);
        }
    }
}
