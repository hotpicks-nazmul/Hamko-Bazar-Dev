<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;

class FaqController extends Controller
{
    /**
     * Display FAQs list with statistics.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        
        // Statistics
        $stats = [
            'total' => Faq::count(),
            'active' => Faq::where('status', 'active')->count(),
            'inactive' => Faq::where('status', 'inactive')->count(),
        ];
        
        // Build query
        $query = Faq::query();
        
        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($status) {
            $query->where('status', $status);
        }
        
        // Get FAQs ordered
        $faqs = $query->ordered()
            ->paginate(25)
            ->appends($request->query());
        
        // AJAX response
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('admin.faqs.partials.table-rows', compact('faqs'))->render();
            
            $pagination = '';
            if (method_exists($faqs, 'hasPages') && $faqs->hasPages()) {
                $pagination = '<div class="d-flex justify-content-center mt-3">' . $faqs->links()->toHtml() . '</div>';
            }
            
            return response()->json([
                'html' => $html,
                'stats' => $stats,
                'pagination' => $pagination,
                'total' => $faqs->total()
            ]);
        }
        
        return view('admin.faqs.index', compact('faqs', 'stats'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.faqs.create');
    }

    /**
     * Store new FAQ.
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        
        // Set sort order
        if (empty($data['sort_order'])) {
            $data['sort_order'] = Faq::max('sort_order') + 1;
        }

        Faq::create($data);

        return redirect()->route('admin.faqs.index')
            ->with('success', 'FAQ created successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(Faq $faq)
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    /**
     * Update FAQ.
     */
    public function update(Request $request, Faq $faq)
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        
        // Set sort order
        if (empty($data['sort_order'])) {
            $data['sort_order'] = Faq::max('sort_order') + 1;
        }

        $faq->update($data);

        return redirect()->route('admin.faqs.index')
            ->with('success', 'FAQ updated successfully.');
    }

    /**
     * Delete FAQ.
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();
        
        return back()->with('success', 'FAQ deleted successfully.');
    }

    /**
     * Toggle FAQ status (AJAX).
     */
    public function toggleStatus(Request $request, Faq $faq)
    {
        $faq->update([
            'status' => $faq->status === 'active' ? 'inactive' : 'active'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'id' => $faq->id,
            'status' => $faq->status,
            'badge' => $faq->status_badge
        ]);
    }

    /**
     * Bulk actions.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'ids' => 'required|array',
            'ids.*' => 'exists:faqs,id',
        ]);

        $action = $request->action;
        $ids = $request->ids;
        $count = count($ids);

        switch ($action) {
            case 'delete':
                Faq::whereIn('id', $ids)->delete();
                $message = "{$count} FAQ(s) deleted successfully.";
                break;

            case 'activate':
                Faq::whereIn('id', $ids)->update(['status' => 'active']);
                $message = "{$count} FAQ(s) activated successfully.";
                break;

            case 'deactivate':
                Faq::whereIn('id', $ids)->update(['status' => 'inactive']);
                $message = "{$count} FAQ(s) deactivated successfully.";
                break;

            default:
                return back()->with('error', 'Invalid action selected.');
        }

        return back()->with('success', $message);
    }

    /**
     * Reorder FAQs.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:faqs,id',
        ]);

        foreach ($request->order as $index => $id) {
            Faq::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'FAQs reordered successfully.'
        ]);
    }
}
