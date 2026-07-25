<?php

namespace App\Observers;

use App\Models\TicketReply;

class TicketReplyObserver
{
    public function created(TicketReply $reply)
    {
        if (!$reply->is_admin_reply) {
            $ticket = $reply->ticket;
            notify_new_ticket($ticket);
        }
    }
}
