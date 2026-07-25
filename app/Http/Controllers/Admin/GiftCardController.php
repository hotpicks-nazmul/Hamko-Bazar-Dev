<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GiftCardController extends Controller
{
    /**
     * Display a listing of the gift cards.
     */
    public function index(Request $request)
    {
        $query = GiftCard::query()->with('user');

        // Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('recipient_email', 'like', "%{$search}%")
                    ->orWhere('sender_name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Sort
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);

        $giftCards = $query->paginate(15);

        $stats = [
            'total' => GiftCard::count(),
            'active' => GiftCard::where('status', 'active')->count(),
            'inactive' => GiftCard::where('status', 'inactive')->count(),
            'expired' => GiftCard::where('status', 'expired')->count(),
            'used' => GiftCard::where('status', 'used')->count(),
        ];

        return view('admin.marketing.gift-cards.index', compact('giftCards', 'stats'));
    }

    /**
     * Show the form for creating a new gift card.
     */
    public function create()
    {
        $users = User::where('role', 'customer')->orderBy('name')->get();
        return view('admin.marketing.gift-cards.create', compact('users'));
    }

    /**
     * Store a newly created gift card in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'balance' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'user_id' => 'nullable|exists:users,id',
            'recipient_email' => 'nullable|email',
            'recipient_name' => 'nullable|string|max:255',
            'sender_name' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'status' => 'required|in:active,inactive,expired,used',
            'expiry_date' => 'nullable|date',
            'usage_limit' => 'nullable|integer|min:1',
            'is_featured' => 'nullable|boolean',
            'background_color' => 'nullable|string|max:20',
            'terms_conditions' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['initial_amount'] = $request->balance;

        // Generate code if not provided
        if (empty($data['code'])) {
            $data['code'] = GiftCard::generateCode();
        }

        GiftCard::create($data);

        return redirect()->route('admin.marketing.gift-cards.index')
            ->with('success', 'Gift card created successfully.');
    }

    /**
     * Show the form for editing the specified gift card.
     */
    public function edit($id)
    {
        $giftCard = GiftCard::findOrFail($id);
        $users = User::where('role', 'customer')->orderBy('name')->get();
        
        return view('admin.marketing.gift-cards.edit', compact('giftCard', 'users'));
    }

    /**
     * Update the specified gift card in storage.
     */
    public function update(Request $request, $id)
    {
        $giftCard = GiftCard::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'balance' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'user_id' => 'nullable|exists:users,id',
            'recipient_email' => 'nullable|email',
            'recipient_name' => 'nullable|string|max:255',
            'sender_name' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'status' => 'required|in:active,inactive,expired,used',
            'expiry_date' => 'nullable|date',
            'usage_limit' => 'nullable|integer|min:1',
            'is_featured' => 'nullable|boolean',
            'background_color' => 'nullable|string|max:20',
            'terms_conditions' => 'nullable|string',
        ]);

        $data = $request->all();

        // If balance is being increased, keep the initial_amount as is
        // If balance is being manually set to 0, mark as used
        if ($request->balance == 0) {
            $data['status'] = 'used';
        }

        $giftCard->update($data);

        return redirect()->route('admin.marketing.gift-cards.index')
            ->with('success', 'Gift card updated successfully.');
    }

    /**
     * Remove the specified gift card from storage.
     */
    public function destroy($id)
    {
        $giftCard = GiftCard::findOrFail($id);
        $giftCard->delete();

        return redirect()->route('admin.marketing.gift-cards.index')
            ->with('success', 'Gift card deleted successfully.');
    }

    /**
     * Toggle the status of the gift card.
     * Only active/inactive can be toggled. Expired and used are terminal states.
     */
    public function toggleStatus($id)
    {
        $giftCard = GiftCard::findOrFail($id);

        if (in_array($giftCard->status, ['expired', 'used'])) {
            return back()->with('error', 'Cannot toggle status of ' . $giftCard->status . ' gift cards.');
        }

        $newStatus = $giftCard->status === 'active' ? 'inactive' : 'active';
        $giftCard->update(['status' => $newStatus]);

        return back()->with('success', 'Gift card status updated.');
    }

    /**
     * Generate a new code for the gift card.
     */
    public function generateCode(Request $request)
    {
        $code = GiftCard::generateCode();
        
        if ($request->ajax()) {
            return response()->json(['code' => $code]);
        }
        
        return $code;
    }
}
