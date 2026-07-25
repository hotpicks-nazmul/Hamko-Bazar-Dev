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
        Schema::create('predefined_messages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->comment('Short title/label for the message');
            $table->text('message')->comment('The actual message content');
            $table->string('category', 100)->nullable()->comment('Category for grouping messages');
            $table->integer('sort_order')->default(0)->comment('Display order');
            $table->boolean('is_active')->default(true)->comment('Whether message is active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predefined_messages');
    }
};
