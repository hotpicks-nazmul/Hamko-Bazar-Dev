<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductQA;
use Illuminate\Http\Request;

class ProductQAController extends Controller
{
    /**
     * Display a listing of Q&A entries.
     */
    public function index(Request $request)
    {
        $query = ProductQA::with(['product', 'user', 'answerer']);

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by product
        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by featured
        if ($request->featured) {
            $query->where('is_featured', $request->featured === 'yes');
        }

        // Sorting
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->per_page ?? 25;
        $qaEntries = $query->paginate($perPage);

        // Statistics
        $stats = [
            'total' => ProductQA::count(),
            'pending' => ProductQA::pending()->count(),
            'answered' => ProductQA::answered()->count(),
            'published' => ProductQA::published()->count(),
        ];

        // Get products for filter dropdown
        $products = Product::select('id', 'name')
            ->whereHas('qa')
            ->orderBy('name')
            ->get();

        // AJAX response
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.product-qa.partials.table-rows', compact('qaEntries'))->render(),
                'pagination' => $qaEntries->links()->toHtml(),
                'stats' => $stats,
            ]);
        }

        return view('admin.product-qa.index', compact('qaEntries', 'stats', 'products'));
    }

    /**
     * Show a specific Q&A entry for answering.
     */
    public function show(ProductQA $product_qa)
    {
        $product_qa->load(['product', 'user', 'answerer']);
        
        // Get related Q&A for the same product
        $relatedQA = ProductQA::where('product_id', $product_qa->product_id)
            ->where('id', '!=', $product_qa->id)
            ->with(['user', 'answerer'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.product-qa.show', compact('product_qa', 'relatedQA'));
    }

    /**
     * Update the Q&A entry (answer the question).
     */
    public function update(Request $request, ProductQA $product_qa)
    {
        $request->validate([
            'answer' => 'required|string|max:2000',
            'status' => 'required|in:pending,answered,published',
            'is_featured' => 'boolean',
        ]);

        $product_qa->update([
            'answer' => $request->answer,
            'answered_by' => auth()->id(),
            'answered_at' => now(),
            'status' => $request->status,
            'is_featured' => $request->has('is_featured'),
        ]);

        return redirect()
            ->route('admin.product-qa.index')
            ->with('success', 'Question answered successfully.');
    }

    /**
     * Remove the Q&A entry.
     */
    public function destroy(ProductQA $product_qa)
    {
        $product_qa->delete();

        return redirect()
            ->route('admin.product-qa.index')
            ->with('success', 'Q&A entry deleted successfully.');
    }

    /**
     * Bulk actions for Q&A entries.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,publish,unpublish,feature,unfeature',
            'ids' => 'required|json',
        ]);

        $ids = json_decode($request->ids, true);

        if (empty($ids)) {
            return redirect()
                ->route('admin.product-qa.index')
                ->with('error', 'No items selected.');
        }

        $count = 0;

        switch ($request->action) {
            case 'delete':
                $count = ProductQA::whereIn('id', $ids)->delete();
                $message = "{$count} Q&A entries deleted.";
                break;

            case 'publish':
                $count = ProductQA::whereIn('id', $ids)->update(['status' => 'published']);
                $message = "{$count} Q&A entries published.";
                break;

            case 'unpublish':
                $count = ProductQA::whereIn('id', $ids)->update(['status' => 'pending']);
                $message = "{$count} Q&A entries unpublished.";
                break;

            case 'feature':
                $count = ProductQA::whereIn('id', $ids)->update(['is_featured' => true]);
                $message = "{$count} Q&A entries featured.";
                break;

            case 'unfeature':
                $count = ProductQA::whereIn('id', $ids)->update(['is_featured' => false]);
                $message = "{$count} Q&A entries unfeatured.";
                break;
        }

        return redirect()
            ->route('admin.product-qa.index')
            ->with('success', $message);
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(ProductQA $product_qa)
    {
        $product_qa->update([
            'is_featured' => !$product_qa->is_featured,
        ]);

        return response()->json([
            'success' => true,
            'is_featured' => $product_qa->is_featured,
        ]);
    }

    /**
     * Quick answer via AJAX.
     */
    public function quickAnswer(Request $request, ProductQA $product_qa)
    {
        $request->validate([
            'answer' => 'required|string|max:2000',
        ]);

        $product_qa->update([
            'answer' => $request->answer,
            'answered_by' => auth()->id(),
            'answered_at' => now(),
            'status' => 'answered',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Answer saved successfully.',
            'answer' => $product_qa->answer,
            'answered_at' => $product_qa->answered_at->format('M d, Y H:i'),
            'answerer' => auth()->user()->name,
        ]);
    }

    /**
     * Update status.
     */
    public function updateStatus(Request $request, ProductQA $product_qa)
    {
        $request->validate([
            'status' => 'required|in:pending,answered,published',
        ]);

        $product_qa->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'status' => $product_qa->status,
        ]);
    }
}
