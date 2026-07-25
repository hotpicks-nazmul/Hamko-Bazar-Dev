<?php

namespace App\Observers;

use App\Models\SellerPayout;

class SellerPayoutObserver
{
    public function created(SellerPayout $payout)
    {
        if ($payout->status === 'pending') {
            notify_payout_request($payout);
        }
    }
}
