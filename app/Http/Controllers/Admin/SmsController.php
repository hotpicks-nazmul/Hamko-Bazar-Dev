<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * SMS Controller for managing SMS communications.
 */
class SmsController extends Controller
{
    /**
     * Display the Bulk SMS page with history and form.
     */
    public function index(Request $request)
    {
        // Get statistics
        $stats = $this->getStats();

        // Get recent SMS history from session (simulated - in production would be from database)
        $smsHistory = Session::get('sms_history', collect([]));

        // Filter SMS history if needed
        if ($request->status) {
            $smsHistory = $smsHistory->where('status', $request->status);
        }

        if ($request->search) {
            $smsHistory = $smsHistory->filter(function ($item) use ($request) {
                return stripos($item['message'], $request->search) !== false;
            });
        }

        // Manually paginate
        $page = $request->get('page', 1);
        $perPage = 25;
        $total = $smsHistory->count();
        $items = $smsHistory->slice(($page - 1) * $perPage, $perPage)->values();
        
        // Create a LengthAwarePaginator
        $smsHistory = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => route('admin.marketing.bulk-sms.index'),
                'query' => $request->query()
            ]
        );

        return view('admin.marketing.bulk-sms.index', compact('stats', 'smsHistory'));
    }

    /**
     * Get recipient count based on type.
     */
    public function getRecipientCount(Request $request)
    {
        $type = $request->type;
        $count = $this->getRecipients($type)->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Send Bulk SMS.
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1600',
            'recipients_type' => 'required|in:all_customers,subscribers,registered_users,specific_numbers',
            'specific_numbers' => 'required_if:recipients_type,specific_numbers|string',
        ], [
            'specific_numbers.required_if' => 'Please enter phone numbers.',
            'message.required' => 'Please enter a message.',
            'message.max' => 'Message cannot exceed 1600 characters.',
        ]);

        $recipients = $this->getRecipients($request->recipients_type);

        // If specific numbers provided, parse and validate them
        if ($request->recipients_type === 'specific_numbers' && $request->specific_numbers) {
            $numbers = array_filter(array_map('trim', explode(',', $request->specific_numbers)));
            $validNumbers = [];
            $invalidNumbers = [];

            foreach ($numbers as $num) {
                if ($this->isValidPhone($num)) {
                    $validNumbers[] = $num;
                } else {
                    $invalidNumbers[] = $num;
                }
            }

            if (empty($validNumbers)) {
                return redirect()->back()->with('error', 'No valid phone numbers provided. Please enter valid Bangladeshi phone numbers.');
            }

            if (!empty($invalidNumbers)) {
                $recipients = collect($validNumbers)->map(function ($num) {
                    return (object) ['phone' => $num, 'name' => 'Specific Number'];
                });
                // Continue with valid numbers, but warn about invalid ones
                session()->flash('warning', 'Some phone numbers were invalid and skipped: ' . implode(', ', $invalidNumbers));
            } else {
                $recipients = collect($validNumbers)->map(function ($num) {
                    return (object) ['phone' => $num, 'name' => 'Specific Number'];
                });
            }
        }

        // Filter out invalid phone numbers
        $validRecipients = $recipients->filter(function ($recipient) {
            return !empty($recipient->phone) && $this->isValidPhone($recipient->phone);
        });

        if ($validRecipients->isEmpty()) {
            return redirect()->back()->with('error', 'No valid recipients found with phone numbers.');
        }

        // Send SMS to each recipient
        $successCount = 0;
        $failedCount = 0;
        $failedNumbers = [];

        foreach ($validRecipients as $recipient) {
            $result = $this->sendSms($recipient->phone, $request->message);
            
            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
                $failedNumbers[] = $recipient->phone;
            }
        }

        // Store in session for history (simulated - in production would save to database)
        $history = Session::get('sms_history', collect([]));
        
        $campaignData = [
            'id' => uniqid('sms_'),
            'message' => $request->message,
            'recipients_type' => $request->recipients_type,
            'total_recipients' => $validRecipients->count(),
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'status' => $failedCount === 0 ? 'sent' : ($successCount > 0 ? 'partial' : 'failed'),
            'sent_at' => now()->toIso8601String(),
            'created_at' => now()->toIso8601String(),
        ];

        $history->prepend($campaignData);
        Session::put('sms_history', $history);

        // Return response
        if ($failedCount > 0 && $successCount > 0) {
            return redirect()->route('admin.marketing.bulk-sms.index')
                ->with('warning', "SMS sent to {$successCount} recipients. {$failedCount} failed.");
        } elseif ($failedCount > 0) {
            return redirect()->route('admin.marketing.bulk-sms.index')
                ->with('error', "Failed to send SMS to {$failedCount} recipients. Please check phone numbers.");
        } else {
            return redirect()->route('admin.marketing.bulk-sms.index')
                ->with('success', "SMS sent successfully to {$successCount} recipients!");
        }
    }

    /**
     * Delete SMS campaign from history.
     */
    public function destroy($id)
    {
        $history = Session::get('sms_history', collect([]));
        
        $history = $history->reject(function ($item) use ($id) {
            return $item['id'] === $id;
        });
        
        Session::put('sms_history', $history);

        return response()->json(['success' => true, 'message' => 'SMS campaign deleted successfully.']);
    }

    /**
     * Get statistics.
     */
    private function getStats()
    {
        $totalCustomers = User::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->count();

        $totalSubscribers = Subscriber::whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where('status', 'active')
            ->count();

        $history = Session::get('sms_history', collect([]));

        return [
            'total_customers' => $totalCustomers,
            'total_subscribers' => $totalSubscribers,
            'total_registered_users' => $totalCustomers, // Same as total_customers
            'total_sent' => $history->count(),
            'successful' => $history->where('status', 'sent')->count(),
            'partial' => $history->where('status', 'partial')->count(),
            'failed' => $history->where('status', 'failed')->count(),
        ];
    }

    /**
     * Get recipients based on type.
     */
    private function getRecipients($type)
    {
        switch ($type) {
            case 'all_customers':
                return User::whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->get(['phone', 'name']);

            case 'subscribers':
                return Subscriber::whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->where('status', 'active')
                    ->get(['phone', 'name']);

            case 'registered_users':
                return User::whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->where('role', 'customer')
                    ->get(['phone', 'name']);

            default:
                return collect([]);
        }
    }

    /**
     * Validate phone number.
     */
    private function isValidPhone($phone)
    {
        // Remove common formatting characters
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Check if it's a valid Bangladesh phone number (starts with 01 and has 11 digits)
        // or international format (+880...)
        return preg_match('/^(\+?880|0)?1[3-9]\d{8}$/', $cleaned);
    }

    /**
     * Send SMS via gateway (simulated).
     * In production, integrate with actual SMS gateway like Twilio, Nexmo, etc.
     */
    private function sendSms($phone, $message)
    {
        // Clean phone number
        $cleanedPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        // Add country code if not present
        if (str_starts_with($cleanedPhone, '0')) {
            $cleanedPhone = '88' . $cleanedPhone;
        } elseif (!str_starts_with($cleanedPhone, '+')) {
            $cleanedPhone = '+' . $cleanedPhone;
        }

        // Log the SMS attempt
        Log::info('SMS Send Attempt', [
            'phone' => $cleanedPhone,
            'message' => $message,
            'timestamp' => now(),
        ]);

        // In production, integrate with actual SMS gateway:
        // Example with Twilio:
        /*
        try {
            $twilio = new \Twilio\Rest\Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $twilio->messages->create($cleanedPhone, [
                'from' => config('services.twilio.from'),
                'body' => $message
            ]);

            return ['success' => true, 'message' => 'SMS sent successfully'];
        } catch (\Exception $e) {
            Log::error('SMS Send Failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
        */

        // For demo purposes, simulate success
        // In production, remove this and use actual gateway
        return ['success' => true, 'message' => 'SMS sent successfully (simulated)'];
    }
}
