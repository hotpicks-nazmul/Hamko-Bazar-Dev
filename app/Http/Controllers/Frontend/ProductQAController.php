<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ProductQA;
use Illuminate\Http\Request;

class ProductQAController extends Controller
{
    /**
     * Store a new question from frontend.
     */
    public function store(Request $request)
    {
        $rules = [
            'product_id' => 'required|exists:products,id',
            'question' => 'required|string|max:1000',
            'questioner_email' => 'nullable|email|max:255',
        ];

        // If user is not authenticated, name is required
        if (!auth()->check()) {
            $rules['questioner_name'] = 'required|string|max:255';
        }

        $request->validate($rules);

        $data = [
            'product_id' => $request->product_id,
            'question' => $request->question,
            'status' => 'pending',
        ];

        // Handle authenticated user
        if (auth()->check()) {
            $data['user_id'] = auth()->id();
            $data['questioner_name'] = $request->has('is_anonymous') ? null : auth()->user()->name;
            $data['is_anonymous'] = $request->has('is_anonymous');
        } else {
            // Guest user
            $data['questioner_name'] = $request->questioner_name;
            $data['questioner_email'] = $request->questioner_email;
            $data['is_anonymous'] = false;
        }

        $qa = ProductQA::create($data);

        return redirect()
            ->back()
            ->with('success', 'Your question has been submitted! We will answer it soon.')
            ->withFragment('qa-section');
    }

    /**
     * Vote on Q&A helpfulness.
     */
    public function vote(Request $request, ProductQA $product_qa)
    {
        $request->validate([
            'is_helpful' => 'required|boolean',
        ]);

        if ($request->is_helpful) {
            $product_qa->increment('helpful_count');
        } else {
            $product_qa->increment('not_helpful_count');
        }

        return response()->json([
            'success' => true,
            'helpful_count' => $product_qa->helpful_count,
            'not_helpful_count' => $product_qa->not_helpful_count,
        ]);
    }
}