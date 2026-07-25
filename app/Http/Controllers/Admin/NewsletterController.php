<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NewsletterController extends Controller
{
    /**
     * Display a listing of newsletters.
     */
    public function index(Request $request)
    {
        $query = Newsletter::query()->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->search) {
            $query->where('subject', 'like', "%{$request->search}%");
        }

        // Status filter
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $newsletters = $query->paginate(25);

        // Statistics
        $stats = [
            'total' => Newsletter::count(),
            'draft' => Newsletter::where('status', 'draft')->count(),
            'sent' => Newsletter::where('status', 'sent')->count(),
            'scheduled' => Newsletter::where('status', 'scheduled')->count(),
            'failed' => Newsletter::where('status', 'failed')->count(),
        ];

        return view('admin.marketing.newsletters.index', compact('newsletters', 'stats'));
    }

    /**
     * Show the form for creating a new newsletter.
     */
    public function create()
    {
        return view('admin.marketing.newsletters.create');
    }

    /**
     * Store a newly created newsletter.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'recipients_type' => 'required|in:all,subscribers,users',
        ]);

        $newsletter = Newsletter::create([
            'subject' => $request->subject,
            'content' => $request->content,
            'recipients_type' => $request->recipients_type,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.marketing.newsletters.index')
            ->with('success', 'Newsletter created successfully.');
    }

    /**
     * Show the form for editing a newsletter.
     */
    public function edit(Newsletter $newsletter)
    {
        return view('admin.marketing.newsletters.edit', compact('newsletter'));
    }

    /**
     * Update the specified newsletter.
     */
    public function update(Request $request, Newsletter $newsletter)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'recipients_type' => 'required|in:all,subscribers,users',
        ]);

        $newsletter->update([
            'subject' => $request->subject,
            'content' => $request->content,
            'recipients_type' => $request->recipients_type,
        ]);

        return redirect()->route('admin.marketing.newsletters.index')
            ->with('success', 'Newsletter updated successfully.');
    }

    /**
     * Remove the specified newsletter.
     */
    public function destroy(Newsletter $newsletter)
    {
        $newsletter->delete();

        return redirect()->route('admin.marketing.newsletters.index')
            ->with('success', 'Newsletter deleted successfully.');
    }

    /**
     * Send the newsletter to recipients.
     */
    public function send(Request $request, Newsletter $newsletter)
    {
        $request->validate([
            'recipients_type' => 'required|in:all,subscribers,users',
        ]);

        // Get recipients based on type
        $recipients = $this->getRecipients($request->recipients_type);

        if ($recipients->isEmpty()) {
            return redirect()->route('admin.marketing.newsletters.index')
                ->with('error', 'No recipients found for the selected type.');
        }

        // Update newsletter recipients type
        $newsletter->update(['recipients_type' => $request->recipients_type]);

        // The content is admin-authored HTML — the preview renders it raw with {!! !!}. Escaping it
        // here meant every recipient received their newsletter with the tags visible as text.
        $emailHtml = $this->wrapEmailWithTemplate($newsletter->content, $newsletter->subject);

        // Send emails with unsubscribe link
        $sentCount = 0;
        $failedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($recipients as $recipient) {
                try {
                    $unsubscribeUrl = route('newsletter.unsubscribe', ['email' => $recipient->email]);
                    $emailWithUnsubscribe = str_replace(
                        '</body>',
                        '<p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666;"><a href="' . $unsubscribeUrl . '" style="color: #666;">Unsubscribe</a> | <a href="{{ unsubscribe_url }}">Manage subscriptions</a></p></body>',
                        $emailHtml
                    );
                    $emailWithUnsubscribe = str_replace('{{ unsubscribe_url }}', $unsubscribeUrl, $emailWithUnsubscribe);

                    Mail::send([], [], function ($message) use ($recipient, $newsletter, $emailWithUnsubscribe) {
                        $message->to($recipient->email)
                            ->subject($newsletter->subject)
                            ->html($emailWithUnsubscribe);
                    });
                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to send newsletter email to ' . $recipient->email . ': ' . $e->getMessage());
                    $failedCount++;
                }
            }

            // Update newsletter status
            $newsletter->update([
                'status' => $failedCount > 0 && $sentCount === 0 ? 'failed' : 'sent',
                'sent_at' => now(),
                'recipients_count' => $sentCount,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.marketing.newsletters.index')
                ->with('error', 'Failed to send newsletter: ' . $e->getMessage());
        }

        if ($sentCount > 0) {
            return redirect()->route('admin.marketing.newsletters.index')
                ->with('success', "Newsletter sent to {$sentCount} recipients." . ($failedCount > 0 ? " {$failedCount} failed." : ''));
        }

        return redirect()->route('admin.marketing.newsletters.index')
            ->with('error', 'Failed to send newsletter. Please try again.');
    }

    /**
     * Wrap email content with a basic HTML template.
     */
    private function wrapEmailWithTemplate($content, $subject)
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . e($subject) . '</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 20px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h2>' . e($subject) . '</h2>
    </div>
    <div class="content">
        ' . $content . '
    </div>
    <div class="footer">
        <p>You are receiving this email because you subscribed to our newsletter.</p>
    </div>
</body>
</html>';
    }

    /**
     * Preview the newsletter.
     */
    public function preview(Newsletter $newsletter)
    {
        return view('admin.marketing.newsletters.preview', compact('newsletter'));
    }

    /**
     * Get recipients based on type.
     */
    private function getRecipients(string $type)
    {
        switch ($type) {
            case 'subscribers':
                $recipients = Subscriber::active()->get();
                break;
            case 'users':
                $recipients = User::where('role', 'customer')->where('status', 'active')->get();
                break;
            case 'all':
            default:
                // Both lists, as plain objects so the loop can read ->email / ->name.
                $subscribers = Subscriber::active()->get()->map(fn ($sub) => (object) ['email' => $sub->email, 'name' => $sub->name]);
                $users = User::where('role', 'customer')->where('status', 'active')->get()->map(fn ($user) => (object) ['email' => $user->email, 'name' => $user->name]);
                $recipients = $subscribers->merge($users);
                break;
        }

        // One email per address: a customer who also subscribed appeared in both lists and received
        // the newsletter twice, and users with no email address threw inside the send loop.
        return $recipients
            ->filter(fn ($r) => !empty($r->email) && filter_var($r->email, FILTER_VALIDATE_EMAIL))
            ->unique(fn ($r) => strtolower(trim($r->email)))
            ->values();
    }

    /**
     * Get subscriber count by type (AJAX).
     */
    public function getRecipientCount(Request $request)
    {
        // Count exactly what send() will send to (deduped, valid addresses).
        $count = $this->getRecipients($request->recipients_type ?? 'all')->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Duplicate a newsletter.
     */
    public function duplicate(Newsletter $newsletter)
    {
        $newNewsletter = $newsletter->replicate();
        $newNewsletter->subject = $newsletter->subject . ' (Copy)';
        $newNewsletter->status = 'draft';
        $newNewsletter->sent_at = null;
        $newNewsletter->recipients_count = 0;
        $newNewsletter->created_by = auth()->id();
        $newNewsletter->save();

        return redirect()->route('admin.marketing.newsletters.index')
            ->with('success', 'Newsletter duplicated successfully.');
    }
}
