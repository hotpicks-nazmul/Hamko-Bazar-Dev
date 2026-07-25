<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmailTemplateController extends Controller
{
    /**
     * Display a listing of email templates.
     */
    public function index(Request $request)
    {
        $query = EmailTemplate::query();

        // Search by subject or slug
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('subject', 'like', "%{$request->search}%")
                    ->orWhere('slug', 'like', "%{$request->search}%");
            });
        }

        // Filter by recipient type
        if ($request->recipient_type) {
            $query->where('recipient_type', $request->recipient_type);
        }

        // Filter by status
        if ($request->status !== null && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        // Sort
        $sort = $request->sort ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($sort, $direction);

        $templates = $query->paginate($request->per_page ?? 15);

        // Get stats
        $stats = [
            'total' => EmailTemplate::count(),
            'active' => EmailTemplate::where('is_active', true)->count(),
            'inactive' => EmailTemplate::where('is_active', false)->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.settings.email-templates.partials.table-rows', compact('templates'))->render(),
                'pagination' => $templates->links()->toHtml(),
                'stats' => $stats,
            ]);
        }

        return view('admin.settings.email-templates.index', compact('templates', 'stats'));
    }

    /**
     * Display the specified email template.
     */
    public function show(EmailTemplate $emailTemplate)
    {
        return view('admin.settings.email-templates.show', compact('emailTemplate'));
    }

    /**
     * Show the form for editing the specified email template.
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        return view('admin.settings.email-templates.edit', compact('emailTemplate'));
    }

    /**
     * Update the specified email template in storage.
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $emailTemplate->update([
            'subject' => $request->subject,
            'body' => $request->body,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.settings.email-templates.index')
            ->with('success', 'Email template updated successfully.');
    }

    /**
     * Toggle the status of the specified email template.
     */
    public function toggleStatus(EmailTemplate $emailTemplate)
    {
        $emailTemplate->update([
            'is_active' => !$emailTemplate->is_active,
        ]);

        $status = $emailTemplate->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Email template {$status} successfully.");
    }

    /**
     * Get email template for API (frontend integration).
     */
    public function getTemplateApi(Request $request)
    {
        $request->validate([
            'slug' => 'required|string',
        ]);

        $template = EmailTemplate::where('slug', $request->slug)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found or inactive',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'template' => [
                'id' => $template->id,
                'slug' => $template->slug,
                'subject' => $template->subject,
                'body' => $template->body,
                'variables' => $template->variables,
                'event' => $template->event,
                'recipient_type' => $template->recipient_type,
            ],
        ]);
    }

    /**
     * Get all active email templates for API.
     */
    public function getAllTemplatesApi()
    {
        $templates = EmailTemplate::active()
            ->select('id', 'slug', 'subject', 'event', 'recipient_type')
            ->get();

        return response()->json([
            'success' => true,
            'templates' => $templates,
        ]);
    }

    /**
     * Render email template with provided variables.
     */
    public function renderTemplate(Request $request)
    {
        $request->validate([
            'slug' => 'required|string',
            'variables' => 'required|array',
        ]);

        $template = EmailTemplate::where('slug', $request->slug)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found or inactive',
            ], 404);
        }

        $rendered = $template->render($request->variables);

        return response()->json([
            'success' => true,
            'rendered' => [
                'subject' => $rendered['subject'],
                'body' => $rendered['body'],
            ],
        ]);
    }

    /**
     * Preview email template with sample data.
     */
    public function preview(Request $request, EmailTemplate $emailTemplate)
    {
        // Get sample data for preview
        $sampleData = $this->getSampleData($emailTemplate);

        // If custom variables provided, merge them
        if ($request->has('variables') && is_array($request->variables)) {
            $sampleData = array_merge($sampleData, $request->variables);
        }

        $rendered = $emailTemplate->render($sampleData);

        return response()->json([
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
            'variables' => $emailTemplate->variables_list,
        ]);
    }

    /**
     * Get sample data for preview.
     */
    private function getSampleData(EmailTemplate $template): array
    {
        return match ($template->slug) {
            'order_confirmation', 'order_shipped', 'order_delivered', 'order_cancelled' => [
                'customer_name' => 'John Doe',
                'order_number' => 'ORD-12345',
                'order_date' => now()->format('F j, Y'),
                'total_amount' => '$99.99',
                'order_items' => ['Product A x 2', 'Product B x 1'],
                'tracking_number' => 'TRACK123456',
                'shipping_method' => 'Standard Shipping',
                'estimated_delivery' => now()->addDays(5)->format('F j, Y'),
                'cancellation_reason' => 'Customer request',
                'refund_amount' => '$99.99',
            ],
            'password_reset' => [
                'customer_name' => 'John Doe',
                'reset_link' => url('/password/reset/token123'),
                'expiry_time' => '60 minutes',
            ],
            'welcome_email' => [
                'customer_name' => 'John Doe',
                'site_name' => getSetting('site_name') ?? config('app.name'),
                'coupon_code' => 'WELCOME20',
            ],
            'refund_processed' => [
                'customer_name' => 'John Doe',
                'order_number' => 'ORD-12345',
                'refund_amount' => '$99.99',
                'refund_method' => 'Original Payment Method',
            ],
            'seller_new_order', 'seller_payout' => [
                'seller_name' => 'Seller Store',
                'order_number' => 'ORD-12345',
                'product_name' => 'Sample Product',
                'quantity' => '2',
                'amount' => '$99.99',
                'payout_id' => 'PAYOUT-123',
                'payment_method' => 'Bank Transfer',
            ],
            'admin_new_order' => [
                'order_number' => 'ORD-12345',
                'customer_name' => 'John Doe',
                'total_amount' => '$99.99',
                'payment_method' => 'Credit Card',
            ],
            'admin_low_stock' => [
                'product_name' => 'Sample Product',
                'current_stock' => '5',
                'sku' => 'SKU-123',
            ],
            'contact_form' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+1234567890',
                'subject' => 'Inquiry',
                'message' => 'This is a sample inquiry message.',
            ],
            'newsletter_subscription' => [
                'email' => 'subscriber@example.com',
            ],
            default => [
                'name' => 'User Name',
                'email' => 'user@example.com',
            ],
        };
    }
}
