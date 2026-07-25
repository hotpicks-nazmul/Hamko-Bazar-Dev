<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCartRecord;
use App\Models\AbandonedCartSettings;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AbandonedCartController extends Controller
{
    /**
     * Display abandoned cart list.
     */
    public function index(Request $request)
    {
        $query = AbandonedCartRecord::with(['user', 'cart'])
            ->orderBy('abandoned_at', 'desc');

        // Filter by status
        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('abandoned_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('abandoned_at', '<=', $request->date_to);
        }

        // Search by email or name
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_email', 'like', "%{$request->search}%")
                    ->orWhere('customer_name', 'like', "%{$request->search}%");
            });
        }

        $records = $query->paginate(25);

        // Get statistics
        $stats = [
            'total' => AbandonedCartRecord::count(),
            'abandoned' => AbandonedCartRecord::where('status', AbandonedCartRecord::STATUS_ABANDONED)->count(),
            'recovered' => AbandonedCartRecord::where('status', AbandonedCartRecord::STATUS_RECOVERED)->count(),
            'pending' => AbandonedCartRecord::where('status', AbandonedCartRecord::STATUS_PENDING)->count(),
            'email_sent' => AbandonedCartRecord::where('status', AbandonedCartRecord::STATUS_EMAIL_SENT)->count(),
            'total_revenue_recovered' => AbandonedCartRecord::where('status', AbandonedCartRecord::STATUS_RECOVERED)->sum('cart_total'),
        ];

        return view('admin.marketing.abandoned-cart.index', compact('records', 'stats'));
    }

    /**
     * Display abandoned cart details.
     */
    public function show($id)
    {
        $record = AbandonedCartRecord::with(['user', 'cart'])->findOrFail($id);
        
        return view('admin.marketing.abandoned-cart.show', compact('record'));
    }

    /**
     * Send manual reminder to customer.
     */
    public function sendReminder(Request $request, $id)
    {
        $record = AbandonedCartRecord::with('cart')->findOrFail($id);

        if (!$record->customer_email) {
            return back()->with('error', 'Customer email not found for this cart.');
        }

        // Get settings
        $settings = AbandonedCartSettings::getSettings();

        // Use max_emails_per_cart from settings instead of hardcoded value
        $maxEmails = $settings->max_emails_per_cart ?? 3;

        if ($record->email_sent_count >= $maxEmails) {
            return back()->with('error', "Maximum reminder emails ({$maxEmails}) already sent for this cart.");
        }

        // Generate recovery link using route helper
        $recoveryLink = route('cart.recover', ['email' => $record->customer_email, 'cart_id' => $record->cart_id]);

        // Prepare email content
        $subject = e($settings->email_subject ?? 'You left something behind!');

        // Build cart items HTML with XSS protection (escape all user-generated content)
        $cartItemsHtml = '';
        if ($record->cart && $record->cart->items) {
            foreach ($record->cart->items as $item) {
                $cartItemsHtml .= '<div style="padding: 10px; border-bottom: 1px solid #eee;">';
                $cartItemsHtml .= '<strong>' . e($item['name']) . '</strong><br>';
                $cartItemsHtml .= 'Qty: ' . (int)$item['quantity'] . ' | Price: ' . format_price($item['price']);
                $cartItemsHtml .= '</div>';
            }
        }

        // Replace placeholders in template
        $emailBody = $settings->email_template ?? '';
        $emailBody = str_replace('{{customer_name}}', e($record->customer_name ?? 'Customer'), $emailBody);
        $emailBody = str_replace('{{cart_items}}', $cartItemsHtml, $emailBody);
        $emailBody = str_replace('{{cart_total}}', format_price($record->cart_total), $emailBody);
        $emailBody = str_replace('{{recovery_link}}', $recoveryLink, $emailBody);
        $emailBody = str_replace('{{shop_name}}', e(getSetting('site_name') ?? config('app.name')), $emailBody);

        // Add discount offer if enabled
        if ($settings->include_discount && $settings->discount_code) {
            $discountHtml = '<p style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">';
            $discountHtml .= '<strong>Special Offer!</strong> Use code <strong>' . e($settings->discount_code) . '</strong>';
            if ($settings->discount_percentage) {
                $discountHtml .= ' to get ' . (int)$settings->discount_percentage . '% off!';
            }
            $discountHtml .= '</p>';
            $emailBody = str_replace('{{discount_offer}}', $discountHtml, $emailBody);
        } else {
            $emailBody = str_replace('{{discount_offer}}', '', $emailBody);
        }

        // Send email
        try {
            Mail::html($emailBody, function ($message) use ($record, $subject) {
                $message->to($record->customer_email)
                    ->subject($subject);
            });
        } catch (\Exception $e) {
            Log::error('Abandoned cart email failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to send recovery email: ' . $e->getMessage());
        }

        // Update record
        $record->update([
            'status' => AbandonedCartRecord::STATUS_EMAIL_SENT,
            'last_email_sent_at' => now(),
            'email_sent_count' => $record->email_sent_count + 1,
        ]);

        // Update cart
        if ($record->cart) {
            $record->cart->update([
                'recovery_email_sent_at' => now(),
                'recovery_email_count' => $record->cart->recovery_email_count + 1,
            ]);
        }

        return back()->with('success', 'Recovery email sent successfully to ' . $record->customer_email);
    }

    /**
     * Mark cart as recovered.
     */
    public function markRecovered(Request $request, $id)
    {
        $record = AbandonedCartRecord::findOrFail($id);

        $record->update([
            'status' => AbandonedCartRecord::STATUS_RECOVERED,
            'recovered_at' => now(),
        ]);

        // Update cart if exists
        if ($record->cart) {
            $record->cart->update([
                'is_recovered' => true,
                'recovered_at' => now(),
            ]);
        }

        return back()->with('success', 'Cart marked as recovered successfully.');
    }

    /**
     * Delete abandoned cart record.
     */
    public function destroy($id)
    {
        $record = AbandonedCartRecord::findOrFail($id);
        $record->delete();

        return redirect()->route('admin.marketing.abandoned-cart.index')
            ->with('success', 'Abandoned cart record deleted successfully.');
    }

    /**
     * Display settings page.
     */
    public function settings()
    {
        $settings = AbandonedCartSettings::first();

        return view('admin.marketing.abandoned-cart.settings', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'abandonment_time' => 'required|integer|min:1',
            'first_email_delay' => 'required|integer|min:0',
            'second_email_delay' => 'required|integer|min:0',
            'max_emails_per_cart' => 'required|integer|min:1|max:10',
        ]);

        $settings = AbandonedCartSettings::first();

        if (!$settings) {
            $settings = new AbandonedCartSettings();
        }

        $settings->fill([
            'is_enabled' => $request->has('is_enabled'),
            'abandonment_time' => $request->abandonment_time,
            'send_recovery_email' => $request->has('send_recovery_email'),
            'first_email_delay' => $request->first_email_delay,
            'second_email_delay' => $request->second_email_delay,
            'max_emails_per_cart' => $request->max_emails_per_cart,
            'email_subject' => $request->email_subject,
            'email_template' => $request->email_template,
            'include_discount' => $request->has('include_discount'),
            'discount_percentage' => $request->discount_percentage,
            'discount_code' => $request->discount_code,
        ]);

        $settings->save();

        return redirect()->route('admin.marketing.abandoned-cart.settings')
            ->with('success', 'Abandoned cart settings updated successfully.');
    }

    /**
     * Display conversion tracking / analytics page.
     */
    public function conversionTracking()
    {
        // Get conversion stats
        $totalAbandoned = AbandonedCartRecord::count();
        $recovered = AbandonedCartRecord::where('status', AbandonedCartRecord::STATUS_RECOVERED)->count();
        $recoveredRevenue = AbandonedCartRecord::where('status', AbandonedCartRecord::STATUS_RECOVERED)->sum('cart_total');
        $emailsSent = AbandonedCartRecord::sum('email_sent_count');
        
        $recoveryRate = $totalAbandoned > 0 ? round(($recovered / $totalAbandoned) * 100, 2) : 0;

        // Get monthly data for chart
        $monthlyData = AbandonedCartRecord::selectRaw('DATE_FORMAT(abandoned_at, "%Y-%m") as month, 
            COUNT(*) as total,
            SUM(CASE WHEN status = "recovered" THEN 1 ELSE 0 END) as recovered,
            SUM(CASE WHEN status = "recovered" THEN cart_total ELSE 0 END) as revenue')
            ->whereNotNull('abandoned_at')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('admin.marketing.abandoned-cart.conversion', compact(
            'totalAbandoned', 'recovered', 'recoveredRevenue', 'emailsSent', 'recoveryRate', 'monthlyData'
        ));
    }

    /**
     * Scan for abandoned carts and create records.
     * This should be called via a scheduled command.
     */
    public function scanAbandonedCarts()
    {
        $settings = AbandonedCartSettings::getSettings();
        
        if (!$settings->is_enabled) {
            return ['message' => 'Abandoned cart is disabled'];
        }

        $abandonmentMinutes = $settings->abandonment_time;
        $cutoffTime = now()->subMinutes($abandonmentMinutes);

        // Find carts that have items but haven't been updated for the abandonment period
        $carts = Cart::whereNotNull('user_id')
            ->whereHas('user')
            ->where('updated_at', '<', $cutoffTime)
            ->where('is_abandoned', false)
            ->whereNotNull('items')
            ->where('items', '!=', '[]')
            ->get();

        foreach ($carts as $cart) {
            // Skip if cart has recent orders (already recovered)
            if ($cart->is_recovered) {
                continue;
            }

            $user = $cart->user;
            
            // Create abandoned cart record
            AbandonedCartRecord::create([
                'cart_id' => $cart->id,
                'user_id' => $user->id,
                'customer_email' => $user->email,
                'customer_name' => $user->name,
                'cart_total' => $cart->getSubtotal(),
                'item_count' => $cart->getItemCount(),
                'abandoned_at' => $cart->updated_at,
                'status' => AbandonedCartRecord::STATUS_ABANDONED,
            ]);

            // Update cart
            $cart->update([
                'is_abandoned' => true,
                'abandoned_at' => $cart->updated_at,
            ]);
        }

        return ['message' => 'Scanned ' . $carts->count() . ' carts'];
    }
}
