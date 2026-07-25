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
        Schema::create('user_searches', function (Blueprint $table) {
            $table->id();
            $table->string('query', 500);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->unsignedInteger('results_count')->default(0);
            $table->boolean('is_autocomplete')->default(false);
            $table->timestamps();

            // Indexes for better query performance
            $table->index('query');
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['query', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_searches');
    }
};
