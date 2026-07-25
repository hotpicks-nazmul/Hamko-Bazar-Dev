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
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            
            // Carrier type and services
            $table->enum('carrier_type', ['international', 'regional', 'local', 'express', 'freight', 'all'])->default('all');
            $table->enum('service_type', ['express', 'standard', 'economy', 'overnight', 'international', 'freight', 'all'])->default('all');
            
            // Contact information
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            
            // API Configuration
            $table->text('api_key')->nullable();
            $table->text('api_secret')->nullable();
            $table->text('api_token')->nullable();
            $table->string('account_number')->nullable();
            $table->enum('api_mode', ['sandbox', 'production'])->default('sandbox');
            $table->boolean('is_api_configured')->default(false);
            
            // Tracking
            $table->string('tracking_url_pattern')->nullable();
            $table->string('tracking_prefix')->nullable();
            
            // Shipping rates
            $table->decimal('base_rate', 10, 2)->default(0);
            $table->decimal('per_kg_rate', 10, 2)->default(0);
            $table->decimal('fuel_surcharge_percent', 5, 2)->default(0);
            $table->decimal('cod_charge', 10, 2)->default(0);
            $table->decimal('free_shipping_threshold', 10, 2)->default(0);
            
            // Coverage
            $table->text('coverage_countries')->nullable();
            $table->text('excluded_countries')->nullable();
            $table->string('estimated_delivery_days')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('supports_tracking')->default(true);
            $table->boolean('supports_cod')->default(false);
            $table->boolean('supports_insurance')->default(false);
            
            // Sorting
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carriers');
    }
};
