<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    public function created(Order $order)
    {
        if ($order->status === 'pending') {
            notify_new_order($order);
        }
    }
}
