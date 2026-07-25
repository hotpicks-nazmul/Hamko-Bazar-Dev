<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL requires changing enum - we'll use a workaround
        DB::statement("ALTER TABLE chats MODIFY COLUMN status ENUM('new', 'open', 'pending', 'closed') DEFAULT 'new'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE chats MODIFY COLUMN status ENUM('open', 'pending', 'closed') DEFAULT 'open'");
    }
};
