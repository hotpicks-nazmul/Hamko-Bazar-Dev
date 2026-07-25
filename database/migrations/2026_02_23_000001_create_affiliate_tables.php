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
        // Affiliate Categories Table
        Schema::create('affiliate_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(5.00);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Affiliate Products Table
        Schema::create('affiliate_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('affiliate_categories')->onDelete('set null');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('commission_rate', 5, 2)->default(5.00);
            $table->string('external_url');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('clicks')->default(0);
            $table->integer('conversions')->default(0);
            $table->timestamps();
        });

        // Affiliates Table (Users who are affiliates)
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('affiliate_code')->unique();
            $table->decimal('commission_rate', 5, 2)->default(5.00);
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->decimal('total_earnings', 10, 2)->default(0.00);
            $table->decimal('pending_balance', 10, 2)->default(0.00);
            $table->string('payment_method')->nullable();
            $table->text('payment_details')->nullable();
            $table->string('website')->nullable();
            $table->text('social_links')->nullable();
            $table->enum('status', ['pending', 'approved', 'suspended', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // Affiliate Links Table
        Schema::create('affiliate_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('affiliate_products')->onDelete('set null');
            $table->string('name');
            $table->string('affiliate_code')->unique();
            $table->text('description')->nullable();
            $table->string('target_url')->nullable();
            $table->integer('clicks')->default(0);
            $table->integer('conversions')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Affiliate Banners Table
        Schema::create('affiliate_banners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image');
            $table->string('target_url')->nullable();
            $table->string('size')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('clicks')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // Affiliate Clicks Table (Track clicks)
        Schema::create('affiliate_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->onDelete('cascade');
            $table->foreignId('link_id')->nullable()->constrained('affiliate_links')->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained('affiliate_products')->onDelete('set null');
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->timestamp('clicked_at');
            $table->timestamps();
        });

        // Affiliate Sales Table (Track conversions)
        Schema::create('affiliate_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->onDelete('cascade');
            $table->foreignId('click_id')->nullable()->constrained('affiliate_clicks')->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained('affiliate_products')->onDelete('set null');
            $table->decimal('sale_amount', 10, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('sale_at');
            $table->timestamps();
        });

        // Affiliate Withdrawals Table
        Schema::create('affiliate_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->text('payment_details');
            $table->text('admin_note')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // Affiliate Requests Table (Registration requests)
        Schema::create('affiliate_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('website')->nullable();
            $table->text('social_links')->nullable();
            $table->text('promotion_methods')->nullable();
            $table->text('admin_note')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_requests');
        Schema::dropIfExists('affiliate_withdrawals');
        Schema::dropIfExists('affiliate_sales');
        Schema::dropIfExists('affiliate_clicks');
        Schema::dropIfExists('affiliate_banners');
        Schema::dropIfExists('affiliate_links');
        Schema::dropIfExists('affiliates');
        Schema::dropIfExists('affiliate_products');
        Schema::dropIfExists('affiliate_categories');
    }
};
