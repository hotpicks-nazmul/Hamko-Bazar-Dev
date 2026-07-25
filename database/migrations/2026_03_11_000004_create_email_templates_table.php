<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->comment('Unique identifier for the template');
            $table->string('subject')->comment('Email subject line');
            $table->text('body')->nullable()->comment('Email body content (HTML)');
            $table->text('variables')->nullable()->comment('JSON array of available variables');
            $table->boolean('is_active')->default(true)->comment('Whether the template is active');
            $table->string('event')->nullable()->comment('Event that triggers this email');
            $table->string('recipient_type')->nullable()->comment('customer, seller, admin, etc.');
            $table->timestamps();
        });

        // Insert default email templates
        DB::table('email_templates')->insert([
            // Customer emails
            [
                'slug' => 'order_confirmation',
                'subject' => 'Order Confirmation - {{order_number}}',
                'body' => '<h1>Thank you for your order!</h1>
<p>Dear {{customer_name}},</p>
<p>Your order has been confirmed. Here are your order details:</p>
<p><strong>Order Number:</strong> {{order_number}}</p>
<p><strong>Order Date:</strong> {{order_date}}</p>
<p><strong>Total Amount:</strong> {{total_amount}}</p>
<p>We will notify you once your order is shipped.</p>
<p>Thank you for shopping with us!</p>',
                'variables' => json_encode(['customer_name', 'order_number', 'order_date', 'total_amount', 'order_items']),
                'is_active' => true,
                'event' => 'order_placed',
                'recipient_type' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'order_shipped',
                'subject' => 'Your Order has been Shipped - {{order_number}}',
                'body' => '<h1>Your Order is on its way!</h1>
<p>Dear {{customer_name}},</p>
<p>Great news! Your order has been shipped.</p>
<p><strong>Order Number:</strong> {{order_number}}</p>
<p><strong>Tracking Number:</strong> {{tracking_number}}</p>
<p><strong>Shipping Method:</strong> {{shipping_method}}</p>
<p>You can track your order using the tracking number above.</p>',
                'variables' => json_encode(['customer_name', 'order_number', 'tracking_number', 'shipping_method', 'estimated_delivery']),
                'is_active' => true,
                'event' => 'order_shipped',
                'recipient_type' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'order_delivered',
                'subject' => 'Order Delivered - {{order_number}}',
                'body' => '<h1>Your Order has been Delivered!</h1>
<p>Dear {{customer_name}},</p>
<p>Your order has been delivered successfully.</p>
<p><strong>Order Number:</strong> {{order_number}}</p>
<p>We hope you enjoy your purchase. Please leave a review to share your experience!</p>',
                'variables' => json_encode(['customer_name', 'order_number', 'delivery_date']),
                'is_active' => true,
                'event' => 'order_delivered',
                'recipient_type' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'order_cancelled',
                'subject' => 'Order Cancelled - {{order_number}}',
                'body' => '<h1>Order Cancelled</h1>
<p>Dear {{customer_name}},</p>
<p>Your order has been cancelled as requested.</p>
<p><strong>Order Number:</strong> {{order_number}}</p>
<p><strong>Cancellation Reason:</strong> {{cancellation_reason}}</p>
<p>If you have any questions, please contact our support team.</p>',
                'variables' => json_encode(['customer_name', 'order_number', 'cancellation_reason', 'refund_amount']),
                'is_active' => true,
                'event' => 'order_cancelled',
                'recipient_type' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'password_reset',
                'subject' => 'Reset Your Password',
                'body' => '<h1>Password Reset Request</h1>
<p>Dear {{customer_name}},</p>
<p>We received a request to reset your password. Click the button below to create a new password:</p>
<p><a href="{{reset_link}}" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">Reset Password</a></p>
<p>Or copy this link: {{reset_link}}</p>
<p>This link will expire in {{expiry_time}}.</p>
<p>If you did not request this, please ignore this email.</p>',
                'variables' => json_encode(['customer_name', 'reset_link', 'expiry_time']),
                'is_active' => true,
                'event' => 'password_reset',
                'recipient_type' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'welcome_email',
                'subject' => 'Welcome to {{site_name}}!',
                'body' => "<h1>Welcome {{customer_name}}!</h1><p>Thank you for joining {{site_name}}!</p><p>We're excited to have you on board. Start exploring our products and enjoy exclusive offers.</p><p>Use this coupon code on your first order: <strong>{{coupon_code}}</strong></p><p>Happy shopping!</p>",
                'variables' => json_encode(['customer_name', 'site_name', 'coupon_code']),
                'is_active' => true,
                'event' => 'customer_registered',
                'recipient_type' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'refund_processed',
                'subject' => 'Refund Processed - {{order_number}}',
                'body' => '<h1>Refund Processed</h1>
<p>Dear {{customer_name}},</p>
<p>Your refund has been processed successfully.</p>
<p><strong>Order Number:</strong> {{order_number}}</p>
<p><strong>Refund Amount:</strong> {{refund_amount}}</p>
<p><strong>Refund Method:</strong> {{refund_method}}</p>
<p>Please allow 5-10 business days for the refund to appear in your account.</p>',
                'variables' => json_encode(['customer_name', 'order_number', 'refund_amount', 'refund_method']),
                'is_active' => true,
                'event' => 'refund_processed',
                'recipient_type' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Seller emails
            [
                'slug' => 'seller_new_order',
                'subject' => 'New Order Received - {{order_number}}',
                'body' => '<h1>New Order Received!</h1>
<p>Dear {{seller_name}},</p>
<p>You have received a new order.</p>
<p><strong>Order Number:</strong> {{order_number}}</p>
<p><strong>Product:</strong> {{product_name}}</p>
<p><strong>Quantity:</strong> {{quantity}}</p>
<p><strong>Amount:</strong> {{amount}}</p>
<p>Please process this order as soon as possible.</p>',
                'variables' => json_encode(['seller_name', 'order_number', 'product_name', 'quantity', 'amount']),
                'is_active' => true,
                'event' => 'new_order',
                'recipient_type' => 'seller',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'seller_payout',
                'subject' => 'Payout Processed - {{payout_id}}',
                'body' => '<h1>Payout Processed</h1>
<p>Dear {{seller_name}},</p>
<p>Your payout has been processed.</p>
<p><strong>Payout ID:</strong> {{payout_id}}</p>
<p><strong>Amount:</strong> {{amount}}</p>
<p><strong>Payment Method:</strong> {{payment_method}}</p>
<p>The amount should reflect in your account within 2-3 business days.</p>',
                'variables' => json_encode(['seller_name', 'payout_id', 'amount', 'payment_method']),
                'is_active' => true,
                'event' => 'payout_processed',
                'recipient_type' => 'seller',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Admin emails
            [
                'slug' => 'admin_new_order',
                'subject' => 'New Order Received - {{order_number}}',
                'body' => '<h1>New Order Alert!</h1>
<p>A new order has been placed.</p>
<p><strong>Order Number:</strong> {{order_number}}</p>
<p><strong>Customer:</strong> {{customer_name}}</p>
<p><strong>Total Amount:</strong> {{total_amount}}</p>
<p><strong>Payment Method:</strong> {{payment_method}}</p>',
                'variables' => json_encode(['order_number', 'customer_name', 'total_amount', 'payment_method']),
                'is_active' => true,
                'event' => 'new_order',
                'recipient_type' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'admin_low_stock',
                'subject' => 'Low Stock Alert - {{product_name}}',
                'body' => '<h1>Low Stock Alert</h1>
<p>The following product is running low on stock:</p>
<p><strong>Product:</strong> {{product_name}}</p>
<p><strong>Current Stock:</strong> {{current_stock}}</p>
<p><strong>SKU:</strong> {{sku}}</p>
<p>Please restock this item soon.</p>',
                'variables' => json_encode(['product_name', 'current_stock', 'sku']),
                'is_active' => true,
                'event' => 'low_stock',
                'recipient_type' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'contact_form',
                'subject' => 'New Contact Form Submission - {{subject}}',
                'body' => '<h1>New Contact Form Submission</h1>
<p><strong>Name:</strong> {{name}}</p>
<p><strong>Email:</strong> {{email}}</p>
<p><strong>Phone:</strong> {{phone}}</p>
<p><strong>Subject:</strong> {{subject}}</p>
<p><strong>Message:</strong></p>
<p>{{message}}</p>',
                'variables' => json_encode(['name', 'email', 'phone', 'subject', 'message']),
                'is_active' => true,
                'event' => 'contact_form',
                'recipient_type' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'slug' => 'newsletter_subscription',
                'subject' => 'New Newsletter Subscription',
                'body' => '<h1>New Newsletter Subscriber</h1>
<p>A new user has subscribed to the newsletter.</p>
<p><strong>Email:</strong> {{email}}</p>',
                'variables' => json_encode(['email']),
                'is_active' => true,
                'event' => 'newsletter_subscription',
                'recipient_type' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
