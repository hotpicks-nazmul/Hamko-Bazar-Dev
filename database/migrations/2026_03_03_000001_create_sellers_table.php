<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds seller-specific fields to users table for B2B functionality.
     * Only adds columns if they don't already exist.
     */
    public function up(): void
    {
        $columnsToAdd = [
            'shop_name' => 'string',
            'shop_description' => 'text',
            'shop_logo' => 'string',
            'shop_banner' => 'string',
            'business_registration_number' => 'string',
            'tax_id' => 'string',
            'bank_name' => 'string',
            'bank_account_number' => 'string',
            'bank_account_name' => 'string',
            'bank_routing_code' => 'string',
            'commission_rate' => 'decimal',
            'pending_balance' => 'decimal',
            'verification_status' => 'enum',
            'verified_at' => 'timestamp',
            'verification_notes' => 'text',
            'contact_person_name' => 'string',
            'contact_person_phone' => 'string',
            'contact_person_email' => 'string',
            'return_address' => 'text',
            'seller_type' => 'enum',
            'company_name' => 'string',
            'company_address' => 'string',
        ];

        foreach ($columnsToAdd as $column => $type) {
            if (!Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $table) use ($column, $type) {
                    switch ($type) {
                        case 'string':
                            $table->string($column)->nullable()->after('role');
                            break;
                        case 'text':
                            $table->text($column)->nullable()->after('role');
                            break;
                        case 'decimal':
                            if ($column === 'commission_rate') {
                                $table->decimal($column, 5, 2)->default(10.00)->nullable()->after('role');
                            } else {
                                $table->decimal($column, 15, 2)->default(0.00)->nullable()->after('role');
                            }
                            break;
                        case 'enum':
                            if ($column === 'verification_status') {
                                $table->enum($column, ['pending', 'verified', 'rejected'])->default('pending')->nullable()->after('role');
                            } else {
                                $table->enum($column, ['individual', 'company'])->default('individual')->nullable()->after('role');
                            }
                            break;
                        case 'timestamp':
                            $table->timestamp($column)->nullable()->after('role');
                            break;
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'shop_name',
                'shop_description',
                'shop_logo',
                'shop_banner',
                'business_registration_number',
                'tax_id',
                'bank_name',
                'bank_account_number',
                'bank_account_name',
                'bank_routing_code',
                'commission_rate',
                'wallet_balance',
                'pending_balance',
                'verification_status',
                'verified_at',
                'verification_notes',
                'contact_person_name',
                'contact_person_phone',
                'contact_person_email',
                'return_address',
                'seller_type',
                'company_name',
                'company_address',
            ]);
        });
    }
};
