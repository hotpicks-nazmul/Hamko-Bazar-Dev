<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product']);

        // Filter by status
        if ($request->status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($request->status === 'approved') {
            $query->where('status', 'approved');
        } elseif ($request->status === 'rejected') {
            $query->where('status', 'rejected');
        }

        // Filter by rating
        if ($request->rating) {
            $query->where('rating', $request->rating);
        }

        // Search by product name or customer name
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                })->orWhereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        $reviews = $query->latest()->paginate(15)->appends($request->query());

        // Get statistics (single query for each stat)
        $stats = [
            'total' => Review::count(),
            'pending' => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'avg_rating' => Review::avg('rating') ?? 0,
        ];

        // AJAX response
        if ($request->ajax()) {
            $html = view('admin.reviews.partials.table-rows', compact('reviews'))->render();
            return response()->json([
                'html' => $html,
                'pagination' => $reviews->links()->toHtml(),
            ]);
        }

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    public function update(Request $request, Review $review)
    {
        $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'title' => 'sometimes|string|max:255',
            'comment' => 'sometimes|string|max:1000',
        ]);

        $review->update($request->only(['rating', 'title', 'comment']));

        return back()->with('success', 'Review updated successfully.');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return back()->with('success', 'Review deleted successfully.');
    }

    public function approve(Review $review)
    {
        $review->update(['status' => 'approved']);
        return back()->with('success', 'Review approved successfully.');
    }

    public function reject(Review $review)
    {
        $review->update(['status' => 'rejected']);
        return back()->with('success', 'Review rejected successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,approve,reject',
            'ids' => 'required|string',
        ]);

        $ids = json_decode($request->ids, true);

        if (!is_array($ids) || empty($ids)) {
            return back()->with('error', 'No reviews selected.');
        }

        switch ($request->action) {
            case 'delete':
                Review::whereIn('id', $ids)->delete();
                $message = count($ids) . ' review(s) deleted successfully.';
                break;
            case 'approve':
                Review::whereIn('id', $ids)->update(['status' => 'approved']);
                $message = count($ids) . ' review(s) approved successfully.';
                break;
            case 'reject':
                Review::whereIn('id', $ids)->update(['status' => 'rejected']);
                $message = count($ids) . ' review(s) rejected successfully.';
                break;
        }

        return back()->with('success', $message);
    }
}
