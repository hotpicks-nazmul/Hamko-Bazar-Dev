<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Support Controller for managing support tickets.
 */
class SupportController extends Controller
{
    /**
     * Display a listing of the tickets.
     */
    public function tickets(Request $request)
    {
        $query = Ticket::with(['user', 'assignedTo'])->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('subject', 'like', "%{$request->search}%")
                  ->orWhere('ticket_number', 'like', "%{$request->search}%")
                  ->orWhereHas('user', function($userQuery) use ($request) {
                      $userQuery->where('name', 'like', "%{$request->search}%")
                                ->orWhere('email', 'like', "%{$request->search}%");
                  });
            });
        }

        // The cards describe the rows on screen, not the whole table: filtering to "open" used to leave
        // the header counting every ticket in the database. Clone BEFORE paginate() applies limit/offset.
        $statsQuery = clone $query;
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'open' => (clone $statsQuery)->where('status', 'open')->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'answered' => (clone $statsQuery)->where('status', 'answered')->count(),
            'solved' => (clone $statsQuery)->where('status', 'solved')->count(),
            'closed' => (clone $statsQuery)->where('status', 'closed')->count(),
        ];

        $perPage = in_array((int) $request->per_page, [25, 50, 100], true) ? (int) $request->per_page : 25;
        $tickets = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.support.tickets.partials.table-rows', compact('tickets'))->render(),
                'stats' => $stats
            ]);
        }

        return view('admin.support.tickets.index', compact('tickets', 'stats'));
    }

    /**
     * Display the specified ticket.
     */
    public function showTicket($id)
    {
        $ticket = Ticket::with(['user', 'assignedTo', 'replies.user', 'replies.admin'])->findOrFail($id);
        
        // Get admin users for assignment dropdown.
        // `users` has no `user_type` column (roles are `role` = admin|super_admin|staff|customer), so the
        // old query was SQLSTATE 42S22 and this page answered 500 for every ticket that existed.
        $admins = User::whereIn('role', ['admin', 'super_admin', 'staff'])
            ->orderBy('name')
            ->get();

        return view('admin.support.tickets.show', compact('ticket', 'admins'));
    }

    /**
     * Update ticket status.
     */
    public function updateTicketStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:open,pending,answered,solved,closed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ticket = Ticket::findOrFail($id);
        $ticket->status = $request->status;
        $ticket->save();

        return response()->json(['success' => true, 'message' => 'Status updated successfully']);
    }

    /**
     * Assign ticket to an admin.
     */
    public function assignTicket(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ticket = Ticket::findOrFail($id);
        $ticket->assigned_to = $request->assigned_to;
        $ticket->save();

        return response()->json(['success' => true, 'message' => 'Ticket assigned successfully']);
    }

    /**
     * Reply to a ticket.
     */
    public function replyTicket(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $ticket = Ticket::findOrFail($id);

        // The screen hides the reply box on a closed ticket, so the route has to refuse it too —
        // otherwise a plain POST drove `status` from closed back to answered and the ticket reopened.
        if ($ticket->status === 'closed') {
            return redirect()->back()->with('error', 'This ticket is closed. Reopen it before replying.');
        }

        $admin = Auth::user();

        // Create the reply
        $reply = new TicketReply();
        $reply->ticket_id = $ticket->id;
        $reply->user_id = $ticket->user_id;
        $reply->admin_id = $admin->id;
        $reply->message = $request->message;
        $reply->is_admin_reply = true;
        $reply->save();

        // Update ticket status
        $ticket->status = 'answered';
        $ticket->save();

        return redirect()->back()->with('success', 'Reply sent successfully.');
    }

    /**
     * Close a ticket.
     */
    public function closeTicket($id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->status === 'closed') {
            return redirect()->back()->with('error', 'This ticket is already closed.');
        }

        $ticket->status = 'closed';
        $ticket->save();

        return redirect()->route('admin.support.tickets.index')
            ->with('success', 'Ticket closed successfully.');
    }

    /**
     * Reopen a closed ticket.
     */
    public function reopenTicket($id)
    {
        $ticket = Ticket::findOrFail($id);

        if ($ticket->status !== 'closed') {
            return redirect()->back()->with('error', 'Only a closed ticket can be reopened.');
        }

        $ticket->status = 'open';
        $ticket->save();

        return redirect()->back()->with('success', 'Ticket reopened successfully.');
    }

    /**
     * Delete a ticket.
     */
    public function destroyTicket($id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return redirect()->route('admin.support.tickets.index')
            ->with('success', 'Ticket deleted successfully.');
    }

    /**
     * Bulk action on tickets.
     */
    public function bulkAction(Request $request)
    {
        // The list's own script posts `JSON.stringify([...])`, i.e. a string — `required|array` rejected
        // exactly the payload the page sends, so Close/Delete/Assign never did anything.
        $rawIds = $request->input('ticket_ids');
        if (is_string($rawIds)) {
            $decoded = json_decode($rawIds, true);
            $rawIds = is_array($decoded) ? $decoded : array_filter(explode(',', $rawIds));
        }

        $ticketIds = array_values(array_filter(array_map('intval', (array) $rawIds)));

        $validator = Validator::make(
            ['action' => $request->input('action'), 'ticket_ids' => $ticketIds, 'assigned_to' => $request->input('assigned_to')],
            [
                'action' => 'required|in:close,delete,assign',
                'ticket_ids' => 'required|array|min:1',
                'assigned_to' => 'nullable|required_if:action,assign|exists:users,id',
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        // only rows that exist, so the count in the flash message is the truth
        $ticketIds = Ticket::whereIn('id', $ticketIds)->pluck('id')->all();
        $action = $request->action;

        switch ($action) {
            case 'close':
                Ticket::whereIn('id', $ticketIds)->update(['status' => 'closed']);
                return redirect()->back()->with('success', count($ticketIds) . ' ticket(s) closed successfully.');
                
            case 'delete':
                Ticket::whereIn('id', $ticketIds)->delete();
                return redirect()->back()->with('success', count($ticketIds) . ' ticket(s) deleted successfully.');
                
            case 'assign':
                Ticket::whereIn('id', $ticketIds)->update(['assigned_to' => $request->assigned_to]);
                return redirect()->back()->with('success', count($ticketIds) . ' ticket(s) assigned successfully.');
                
            default:
                return redirect()->back();
        }
    }

    /**
     * Display product queries (placeholder for now).
     */
    public function productQueries()
    {
        return view('admin.support.product-queries.index');
    }

    /**
     * Reply to product query (placeholder for now).
     */
    public function replyQuery(Request $request, $id)
    {
        return redirect()->back()
            ->with('success', 'Query reply sent successfully.');
    }
}
