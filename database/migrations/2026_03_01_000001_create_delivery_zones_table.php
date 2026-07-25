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
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('region')->nullable(); // e.g., Dhaka Metro, Chittagong Division
            $table->string('country')->nullable();
            $table->string('state')->nullable(); // Division/Region
            $table->string('city')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->enum('area_type', ['nationwide', 'regional', 'city', 'district', 'thana', 'zone'])->default('zone');
            
            // COD Settings
            $table->boolean('cod_enabled')->default(true);
            $table->decimal('cod_charge', 10, 2)->default(0);
            $table->enum('cod_charge_type', ['flat', 'percentage'])->default('flat');
            
            // Free Shipping
            $table->boolean('free_shipping_enabled')->default(false);
            $table->decimal('free_shipping_threshold', 10, 2)->default(0);
            
            // Shipping Cost
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->enum('shipping_cost_type', ['flat', 'weight', 'free'])->default('flat');
            
            // Order Limits
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->decimal('max_order_weight', 10, 2)->nullable(); // in kg
            
            // Delivery Time
            $table->tinyInteger('estimated_days')->default(3);
            $table->string('delivery_time_start')->nullable(); // e.g., "9 AM"
            $table->string('delivery_time_end')->nullable(); // e.g., "6 PM"
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            
            // Coordinates for map-based zones (JSON)
            $table->json('coordinates')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('is_active');
            $table->index('is_default');
            $table->index('area_type');
            $table->index(['country', 'state', 'city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};
