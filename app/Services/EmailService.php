<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send an email using a template.
     *
     * @param string $templateSlug The template slug to use
     * @param string $to The recipient email address
     * @param array $variables Variables to replace in the template
     * @param string|null $subject Override the subject line
     * @return bool
     */
    public function send(string $templateSlug, string $to, array $variables = [], ?string $subject = null): bool
    {
        try {
            // Find the template
            $template = EmailTemplate::findBySlug($templateSlug);
            
            if (!$template) {
                Log::warning("Email template not found: {$templateSlug}");
                return false;
            }
            
            if (!$template->is_active) {
                Log::info("Email template is inactive: {$templateSlug}");
                return false;
            }
            
            // Render the template
            $rendered = $template->render($variables);
            
            // Use custom subject if provided, otherwise use template subject
            $emailSubject = $subject ?? $rendered['subject'];
            
            // Send the email
            Mail::html($rendered['body'], function ($message) use ($to, $emailSubject) {
                $message->to($to)
                    ->subject($emailSubject);
            });
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send an order confirmation email.
     *
     * @param string $to Recipient email
     * @param array $orderData Order details
     * @return bool
     */
    public function sendOrderConfirmation(string $to, array $orderData): bool
    {
        return $this->send('order_confirmation', $to, [
            'customer_name' => $orderData['customer_name'] ?? 'Valued Customer',
            'order_number' => $orderData['order_number'] ?? '',
            'order_date' => $orderData['order_date'] ?? now()->format('F j, Y'),
            'total_amount' => $orderData['total_amount'] ?? '',
            'order_items' => $orderData['order_items'] ?? [],
        ]);
    }

    /**
     * Send an order shipped email.
     *
     * @param string $to Recipient email
     * @param array $orderData Order details
     * @return bool
     */
    public function sendOrderShipped(string $to, array $orderData): bool
    {
        return $this->send('order_shipped', $to, [
            'customer_name' => $orderData['customer_name'] ?? 'Valued Customer',
            'order_number' => $orderData['order_number'] ?? '',
            'tracking_number' => $orderData['tracking_number'] ?? '',
            'shipping_method' => $orderData['shipping_method'] ?? '',
            'estimated_delivery' => $orderData['estimated_delivery'] ?? '',
        ]);
    }

    /**
     * Send a password reset email.
     *
     * @param string $to Recipient email
     * @param array $data Reset data
     * @return bool
     */
    public function sendPasswordReset(string $to, array $data): bool
    {
        return $this->send('password_reset', $to, [
            'customer_name' => $data['customer_name'] ?? 'User',
            'reset_link' => $data['reset_link'] ?? '',
            'expiry_time' => $data['expiry_time'] ?? '60 minutes',
        ]);
    }

    /**
     * Send a welcome email.
     *
     * @param string $to Recipient email
     * @param array $data User data
     * @return bool
     */
    public function sendWelcomeEmail(string $to, array $data): bool
    {
        return $this->send('welcome_email', $to, [
            'customer_name' => $data['customer_name'] ?? 'New User',
            'site_name' => $data['site_name'] ?? config('app.name'),
            'coupon_code' => $data['coupon_code'] ?? '',
        ]);
    }

    /**
     * Send a contact form notification.
     *
     * @param string $to Recipient email (admin)
     * @param array $data Contact form data
     * @return bool
     */
    public function sendContactFormNotification(string $to, array $data): bool
    {
        return $this->send('contact_form', $to, [
            'name' => $data['name'] ?? '',
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'subject' => $data['subject'] ?? '',
            'message' => $data['message'] ?? '',
        ]);
    }

    /**
     * Get template by slug.
     *
     * @param string $slug
     * @return EmailTemplate|null
     */
    public function getTemplate(string $slug): ?EmailTemplate
    {
        return EmailTemplate::findBySlug($slug);
    }

    /**
     * Get all active templates.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllActiveTemplates()
    {
        return EmailTemplate::active()->get();
    }

    /**
     * Get templates by event.
     *
     * @param string $event
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTemplatesByEvent(string $event)
    {
        return EmailTemplate::forEvent($event)->active()->get();
    }

    /**
     * Get templates by recipient type.
     *
     * @param string $recipientType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTemplatesByRecipient(string $recipientType)
    {
        return EmailTemplate::forRecipientType($recipientType)->active()->get();
    }
}
