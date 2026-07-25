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

        $tickets = $query->paginate(25);

        // Stats for the view
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'open')->count(),
            'pending' => Ticket::where('status', 'pending')->count(),
            'answered' => Ticket::where('status', 'answered')->count(),
            'solved' => Ticket::where('status', 'solved')->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
        ];

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
        
        // Get admin users for assignment dropdown
        $admins = User::where('user_type', 'admin')
            ->orWhere('is_super_admin', true)
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
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:close,delete,assign',
            'ticket_ids' => 'required|array|min:1',
            'assigned_to' => 'nullable|required_if:action,assign|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $ticketIds = $request->ticket_ids;
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
