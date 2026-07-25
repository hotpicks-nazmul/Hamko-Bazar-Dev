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
        // Add abandoned cart fields to carts table
        Schema::table('carts', function (Blueprint $table) {
            $table->timestamp('abandoned_at')->nullable()->after('updated_at')->comment('Time when cart was marked as abandoned');
            $table->boolean('is_abandoned')->default(false)->after('abandoned_at')->comment('Whether cart is marked as abandoned');
            $table->timestamp('recovery_email_sent_at')->nullable()->after('is_abandoned')->comment('When recovery email was last sent');
            $table->integer('recovery_email_count')->default(0)->after('recovery_email_sent_at')->comment('Number of recovery emails sent');
            $table->boolean('is_recovered')->default(false)->after('recovery_email_count')->comment('Whether cart was recovered');
            $table->timestamp('recovered_at')->nullable()->after('is_recovered')->comment('Time when cart was recovered');
        });

        // Create abandoned cart records table for detailed tracking
        Schema::create('abandoned_cart_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_email')->nullable();
            $table->string('customer_name')->nullable();
            $table->decimal('cart_total', 10, 2)->default(0);
            $table->integer('item_count')->default(0);
            $table->timestamp('abandoned_at')->nullable();
            $table->enum('status', ['pending', 'abandoned', 'email_sent', 'recovered', 'failed'])->default('pending');
            $table->timestamp('last_email_sent_at')->nullable();
            $table->integer('email_sent_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('customer_email');
            $table->index('status');
            $table->index('abandoned_at');
        });

        // Create abandoned cart settings table
        Schema::create('abandoned_cart_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(false)->comment('Enable/disable abandoned cart recovery');
            $table->integer('abandonment_time')->default(60)->comment('Minutes after which cart is considered abandoned');
            $table->boolean('send_recovery_email')->default(true)->comment('Send recovery email to customers');
            $table->integer('first_email_delay')->default(60)->comment('Minutes to wait before first recovery email');
            $table->integer('second_email_delay')->default(1440)->comment('Minutes to wait before second recovery email (24 hours default)');
            $table->integer('max_emails_per_cart')->default(3)->comment('Maximum recovery emails per cart');
            $table->text('email_subject')->nullable()->comment('Recovery email subject');
            $table->text('email_template')->nullable()->comment('Recovery email template');
            $table->boolean('include_discount')->default(false)->comment('Include discount in recovery email');
            $table->decimal('discount_percentage', 5, 2)->nullable()->comment('Discount percentage for recovered carts');
            $table->string('discount_code')->nullable()->comment('Discount code for recovered carts');
            $table->timestamps();
        });

        // Insert default settings
        DB::table('abandoned_cart_settings')->insert([
            'is_enabled' => false,
            'abandonment_time' => 60,
            'send_recovery_email' => true,
            'first_email_delay' => 60,
            'second_email_delay' => 1440,
            'max_emails_per_cart' => 3,
            'email_subject' => 'You left something behind! Complete your purchase now.',
            'email_template' => '<h2>Hi {{customer_name}},</h2>' .
                '<p>We noticed you left some items in your cart. Don\'t miss out on your favorites!</p>' .
                '<p>Your cart items:</p>' .
                '{{cart_items}}' .
                '<p><strong>Total: {{cart_total}}</strong></p>' .
                '<p><a href="{{recovery_link}}">Click here to complete your purchase</a></p>' .
                '{{discount_offer}}' .
                '<p>Best regards,<br>{{shop_name}}</p>',
            'include_discount' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abandoned_cart_settings');
        Schema::dropIfExists('abandoned_cart_records');
        
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn([
                'abandoned_at',
                'is_abandoned',
                'recovery_email_sent_at',
                'recovery_email_count',
                'is_recovered',
                'recovered_at',
            ]);
        });
    }
};
