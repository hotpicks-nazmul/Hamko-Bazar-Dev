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
        Schema::table('products', function (Blueprint $table) {
            // Digital product specific fields
            $table->string('file_name')->nullable()->after('download_link');
            $table->string('file_path')->nullable()->after('file_name');
            $table->unsignedBigInteger('file_size')->nullable()->after('file_path');
            $table->string('file_type')->nullable()->after('file_size');
            $table->string('file_format')->nullable()->after('file_type');
            $table->integer('download_limit')->nullable()->after('file_format');
            $table->integer('download_expiry_days')->nullable()->after('download_limit');
            $table->text('installation_instructions')->nullable()->after('download_expiry_days');
            $table->text('system_requirements')->nullable()->after('installation_instructions');
            $table->string('version')->nullable()->after('system_requirements');
            $table->string('license_type')->nullable()->after('version');
            $table->json('additional_files')->nullable()->after('license_type');
            $table->boolean('requires_license_key')->default(false)->after('additional_files');
            $table->boolean('auto_generate_license')->default(false)->after('requires_license_key');
            
            // Index for digital products
            $table->index('is_digital');
        });

        // Create table for digital product downloads tracking
        Schema::create('digital_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('license_key')->nullable();
            $table->integer('download_count')->default(0);
            $table->integer('max_downloads')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_download_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'product_id', 'order_id']);
            $table->index('license_key');
        });

        // Create table for license keys
        Schema::create('license_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('license_key')->unique();
            $table->enum('status', ['available', 'used', 'disabled'])->default('available');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_keys');
        Schema::dropIfExists('digital_downloads');
        
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'file_name',
                'file_path',
                'file_size',
                'file_type',
                'file_format',
                'download_limit',
                'download_expiry_days',
                'installation_instructions',
                'system_requirements',
                'version',
                'license_type',
                'additional_files',
                'requires_license_key',
                'auto_generate_license',
            ]);
            $table->dropIndex(['is_digital']);
        });
    }
};