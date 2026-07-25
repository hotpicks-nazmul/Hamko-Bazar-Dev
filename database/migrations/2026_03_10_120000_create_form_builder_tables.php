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
        // Forms table
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('title')->nullable();
            $table->text('success_message')->nullable();
            $table->string('submit_button_text')->default('Submit');
            $table->string('redirect_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('show_on_frontend')->default(true);
            $table->integer('submissions_count')->default(0);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Form Fields table
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
            $table->string('label');
            $table->string('name');
            $table->string('type'); // text, textarea, email, phone, number, select, radio, checkbox, date, time, file, hidden
            $table->text('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_unique')->default(false);
            $table->string('validation_rules')->nullable(); // JSON string of validation rules
            $table->text('options')->nullable(); // JSON string for select/radio/checkbox options
            $table->integer('width')->default(12); // Bootstrap grid width (1-12)
            $table->integer('order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_editable')->default(true);
            $table->string('default_value')->nullable();
            $table->timestamps();
            
            $table->unique(['form_id', 'name']);
        });

        // Form Submissions table
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->onDelete('cascade');
            $table->string('user_type')->nullable(); // guest, user, customer
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('guest_email')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->text('data'); // JSON string of form data
            $table->text('notes')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
    }
};
