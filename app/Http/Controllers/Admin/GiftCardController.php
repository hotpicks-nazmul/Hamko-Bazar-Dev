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

        // Sort — whitelisted (?sort=nope was SQLSTATE 42S22 and ?direction=sideways a syntax error).
        $sortable = ['code', 'title', 'balance', 'initial_amount', 'status', 'expiry_date', 'created_at'];
        $sort = in_array($request->sort, $sortable, true) ? $request->sort : 'created_at';
        $direction = strtolower((string) $request->direction) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $direction);

        $statsQuery = clone $query;

        $giftCards = $query->paginate(15);

        // Nothing ever writes status='expired', so that card was permanently 0 while cards sat past
        // their expiry date. Count the stored state and the dates that have passed.
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->where('status', 'active')
                ->where(fn ($q) => $q->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', now()))->count(),
            'inactive' => (clone $statsQuery)->where('status', 'inactive')->count(),
            'expired' => (clone $statsQuery)->where(function ($q) {
                $q->where('status', 'expired')->orWhere(function ($late) {
                    $late->whereIn('status', ['active', 'inactive'])
                        ->whereNotNull('expiry_date')
                        ->whereDate('expiry_date', '<', now());
                });
            })->count(),
            'used' => (clone $statsQuery)->where('status', 'used')->count(),
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
            'code' => 'nullable|string|max:50|unique:gift_cards,code',
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

        $data = $request->only([
            'code', 'title', 'description', 'balance', 'discount_type', 'discount_value',
            'min_order_amount', 'max_discount_amount', 'user_id', 'recipient_email', 'recipient_name',
            'sender_name', 'message', 'status', 'expiry_date', 'usage_limit', 'is_featured',
            'background_color', 'terms_conditions',
        ]);
        $data['initial_amount'] = $request->balance;
        $data['is_featured'] = $request->boolean('is_featured');

        // Generate a code that is actually free (the column carries a UNIQUE index).
        if (empty($data['code'])) {
            do {
                $code = GiftCard::generateCode();
            } while (GiftCard::where('code', $code)->exists());
            $data['code'] = $code;
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
            'code' => 'required|string|max:50|unique:gift_cards,code,' . $giftCard->id,
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

        $data = $request->only([
            'code', 'title', 'description', 'balance', 'discount_type', 'discount_value',
            'min_order_amount', 'max_discount_amount', 'user_id', 'recipient_email', 'recipient_name',
            'sender_name', 'message', 'status', 'expiry_date', 'usage_limit', 'is_featured',
            'background_color', 'terms_conditions',
        ]);
        $data['is_featured'] = $request->boolean('is_featured');

        // A card with no balance is "used", and a spent card cannot be switched back to active
        // while it still holds nothing.
        $balance = (float) $request->balance;
        if ($balance <= 0) {
            $data['status'] = 'used';
        } elseif ($giftCard->status === 'used') {
            $data['status'] = 'active';
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

        // Also cover the card that is still 'active' in the column but past its expiry date, and the
        // one with nothing left on it: the list now shows them as ended, so the route must agree.
        if (in_array($giftCard->status, ['expired', 'used']) || $giftCard->isExpired() || $giftCard->isFullyUsed()) {
            return back()->with('error', 'Cannot toggle this gift card: ' . ($giftCard->isExpired()
                ? 'it passed its expiry date on ' . $giftCard->expiry_date->format('d M, Y')
                : ($giftCard->isFullyUsed() ? 'its balance is spent' : 'its status is ' . $giftCard->status)) . '.');
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
