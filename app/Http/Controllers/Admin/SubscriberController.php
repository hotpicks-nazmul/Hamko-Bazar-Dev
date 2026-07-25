<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class SubscriberController extends Controller
{
    /**
     * Display a listing of subscribers.
     */
    public function index(Request $request)
    {
        $query = Subscriber::query()->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('email', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $subscribers = $query->paginate(25);

        // Statistics
        $stats = [
            'total' => Subscriber::count(),
            'active' => Subscriber::where('status', 'active')->count(),
            'unsubscribed' => Subscriber::where('status', 'unsubscribed')->count(),
        ];

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.marketing.subscribers.partials.table-rows', compact('subscribers'))->render(),
                'pagination' => $subscribers->links()->toHtml(),
                'stats' => $stats
            ]);
        }

        return view('admin.marketing.subscribers.index', compact('subscribers', 'stats'));
    }

    /**
     * Store a newly created subscriber.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:subscribers,email',
            'name' => 'nullable|string|max:255',
        ]);

        Subscriber::create([
            'email' => $request->email,
            'name' => $request->name,
            'status' => 'active',
        ]);

        return redirect()->route('admin.marketing.subscribers.index')
            ->with('success', 'Subscriber added successfully.');
    }

    /**
     * Remove the specified subscriber.
     */
    public function destroy(Subscriber $subscriber)
    {
        $subscriber->delete();

        return redirect()->route('admin.marketing.subscribers.index')
            ->with('success', 'Subscriber removed successfully.');
    }

    /**
     * Unsubscribe a subscriber.
     */
    public function unsubscribe(Subscriber $subscriber)
    {
        $subscriber->unsubscribe();

        return redirect()->route('admin.marketing.subscribers.index')
            ->with('success', 'Subscriber unsubscribed successfully.');
    }

    /**
     * Resubscribe a subscriber.
     */
    public function resubscribe(Subscriber $subscriber)
    {
        $subscriber->resubscribe();

        return redirect()->route('admin.marketing.subscribers.index')
            ->with('success', 'Subscriber resubscribed successfully.');
    }

    /**
     * Export subscribers.
     */
    public function export(Request $request)
    {
        $query = Subscriber::query();

        // Apply filters
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $subscribers = $query->get();

        // Create CSV content with proper escaping to prevent CSV injection
        $csvContent = "Email,Name,Status,Subscribed Date,Unsubscribed Date\n";

        foreach ($subscribers as $subscriber) {
            $csvContent .= $this->escapeCsvValue($subscriber->email) . ',';
            $csvContent .= $this->escapeCsvValue($subscriber->name) . ',';
            $csvContent .= $this->escapeCsvValue($subscriber->status) . ',';
            $csvContent .= $this->escapeCsvValue($subscriber->created_at) . ',';
            $csvContent .= $this->escapeCsvValue($subscriber->unsubscribed_at ? $subscriber->unsubscribed_at : '') . "\n";
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=subscribers_export_' . date('Y-m-d_His') . '.csv',
        ];

        return response($csvContent, 200, $headers);
    }

    /**
     * Escape a value for CSV to prevent CSV injection attacks.
     */
    private function escapeCsvValue($value)
    {
        if ($value === null) {
            return '';
        }

        $value = (string) $value;

        // If value contains comma, quote, newline, or starts with =, +, -, @, tab
        if (preg_match('/[,"\n\r\t^=+\-@]/', $value)) {
            // Escape quotes by doubling them and wrap in quotes
            $value = '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }

    /**
     * Get subscriber count (AJAX).
     */
    public function getCount()
    {
        $count = Subscriber::active()->count();
        return response()->json(['count' => $count]);
    }
}
