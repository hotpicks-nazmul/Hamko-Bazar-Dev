<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('billing_email')->nullable()->change();
            $table->string('shipping_email')->nullable()->change();
            $table->string('billing_postcode')->nullable()->change();
            $table->string('shipping_postcode')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('billing_email')->nullable(false)->change();
            $table->string('shipping_email')->nullable(false)->change();
            $table->string('billing_postcode')->nullable(false)->change();
            $table->string('shipping_postcode')->nullable(false)->change();
        });
    }
};
