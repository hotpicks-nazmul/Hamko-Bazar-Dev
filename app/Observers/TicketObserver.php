<?php

namespace App\Observers;

use App\Models\Ticket;

class TicketObserver
{
    public function created(Ticket $ticket)
    {
        notify_new_ticket($ticket);
    }
}
