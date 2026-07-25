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
        Schema::create('delivery_boys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique();
            $table->string('photo')->nullable();
            $table->text('address')->nullable();
            $table->string('vehicle_type')->nullable(); // bicycle, bike, car, van, truck
            $table->string('vehicle_number')->nullable();
            $table->string('license_number')->nullable();
            $table->string('national_id')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->decimal('salary', 10, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0); // Percentage
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->integer('total_deliveries')->default(0);
            $table->integer('successful_deliveries')->default(0);
            $table->integer('failed_deliveries')->default(0);
            $table->enum('status', ['active', 'inactive', 'on_leave', 'suspended'])->default('active');
            $table->text('notes')->nullable();
            $table->boolean('is_available')->default(true);
            $table->time('shift_start')->nullable();
            $table->time('shift_end')->nullable();
            $table->foreignId('zone_id')->nullable()->constrained('delivery_zones')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_boys');
    }
};
