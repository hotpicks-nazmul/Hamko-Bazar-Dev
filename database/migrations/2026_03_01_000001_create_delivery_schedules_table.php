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
        Schema::create('delivery_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->tinyInteger('day_of_week')->nullable()->comment('0=Sunday, 6=Saturday, 7=Everyday');
            $table->time('start_time');
            $table->time('end_time');
            $table->time('cutoff_time')->nullable()->comment('Order must be placed before this time');
            $table->enum('type', ['same_day', 'next_day', 'express', 'scheduled'])->default('scheduled');
            $table->boolean('is_active')->default(true);
            $table->integer('max_orders')->nullable()->comment('Maximum orders per slot');
            $table->decimal('additional_fee', 10, 2)->default(0);
            $table->decimal('min_order_amount', 10, 2)->default(0);
            $table->json('delivery_zones')->nullable();
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_to')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'type', 'day_of_week']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_schedules');
    }
};
