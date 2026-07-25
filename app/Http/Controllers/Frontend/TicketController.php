<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    /**
     * Display a listing of the user's tickets.
     */
    public function index()
    {
        $tickets = Ticket::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('themes.general.tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create()
    {
        return view('themes.general.tickets.create');
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|min:5|max:255',
            'category' => 'required|in:general,order,payment,shipping,return,refund,technical,other',
            'priority' => 'required|in:low,medium,high,urgent',
            'description' => 'required|string|min:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $ticket = new Ticket();
        $ticket->ticket_number = Ticket::generateTicketNumber();
        $ticket->user_id = Auth::id();
        $ticket->subject = $request->subject;
        $ticket->description = $request->description;
        $ticket->category = $request->category;
        $ticket->priority = $request->priority;
        $ticket->status = 'open';
        $ticket->save();

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Your support ticket has been created. Our team will respond shortly.');
    }

    /**
     * Display the specified ticket.
     */
    public function show($id)
    {
        $ticket = Ticket::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['replies.user', 'replies.admin'])
            ->firstOrFail();

        return view('themes.general.tickets.show', compact('ticket'));
    }

    /**
     * Reply to a ticket.
     */
    public function reply(Request $request, $id)
    {
        $ticket = Ticket::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Don't allow replies to closed tickets
        if ($ticket->status === 'closed') {
            return redirect()->back()
                ->with('error', 'This ticket is closed. Please create a new ticket if you need further assistance.');
        }

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $reply = new TicketReply();
        $reply->ticket_id = $ticket->id;
        $reply->user_id = Auth::id();
        $reply->admin_id = null;
        $reply->message = $request->message;
        $reply->is_admin_reply = false;
        $reply->save();

        // Update ticket status to open if it was solved
        if ($ticket->status === 'solved') {
            $ticket->status = 'open';
            $ticket->save();
        }

        return redirect()->back()
            ->with('success', 'Your reply has been sent.');
    }

    /**
     * Close a ticket.
     */
    public function close($id)
    {
        $ticket = Ticket::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $ticket->status = 'closed';
        $ticket->save();

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket closed successfully.');
    }
}
