<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\ReviewVote;
use App\Models\Product;

class ReviewController extends Controller
{
    /**
     * Store a new review.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'comment' => 'required|string|max:1000',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Check if user has purchased this product
        $hasPurchased = auth()->user()->orders()
            ->whereHas('items', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            })
            ->where('status', 'delivered')
            ->exists();

        if (!$hasPurchased) {
            return back()->with('error', 'You can only review products you have purchased.');
        }

        // Check if user already reviewed this product
        $existingReview = Review::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingReview) {
            return back()->with('error', 'You have already reviewed this product.');
        }

        Review::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
            'status' => 'pending',
            'helpful_count' => 0,
            'not_helpful_count' => 0,
        ]);

        return back()->with('success', 'Review submitted successfully. It will be visible after approval.');
    }

    /**
     * Update a review.
     */
    public function update(Request $request, Review $review)
    {
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'comment' => 'required|string|max:1000',
        ]);

        $review->update([
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
            'is_approved' => false,
        ]);

        return back()->with('success', 'Review updated successfully.');
    }

    /**
     * Delete a review.
     */
    public function destroy(Review $review)
    {
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $review->delete();

        return back()->with('success', 'Review deleted successfully.');
    }

    /**
     * Vote on a review (helpful/not helpful).
     */
    public function vote(Request $request, Review $review)
    {
        $request->validate([
            'is_helpful' => 'required|boolean',
        ]);

        $userId = auth()->id();

        // Check if user has already voted
        $existingVote = $review->getUserVote($userId);

        if ($existingVote) {
            // If same vote, remove it
            if ($existingVote->is_helpful === (bool) $request->is_helpful) {
                $existingVote->delete();
                
                if ($request->is_helpful) {
                    $review->decrement('helpful_count');
                } else {
                    $review->decrement('not_helpful_count');
                }
                
                $message = 'Vote removed.';
            } else {
                // Change vote
                $existingVote->update(['is_helpful' => $request->is_helpful]);
                
                if ($request->is_helpful) {
                    $review->increment('helpful_count');
                    $review->decrement('not_helpful_count');
                } else {
                    $review->increment('not_helpful_count');
                    $review->decrement('helpful_count');
                }
                
                $message = 'Vote updated.';
            }
        } else {
            // Create new vote
            ReviewVote::create([
                'review_id' => $review->id,
                'user_id' => $userId,
                'is_helpful' => $request->is_helpful,
            ]);

            if ($request->is_helpful) {
                $review->increment('helpful_count');
            } else {
                $review->increment('not_helpful_count');
            }

            $message = 'Thank you for your feedback!';
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'helpful_count' => $review->fresh()->helpful_count,
                'not_helpful_count' => $review->fresh()->not_helpful_count,
            ]);
        }

        return back()->with('success', $message);
    }
}
