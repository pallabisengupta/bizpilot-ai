<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('plans')->where('currency', 'USD')->update(['currency' => 'INR']);
        DB::statement("ALTER TABLE plans MODIFY currency CHAR(3) NOT NULL DEFAULT 'INR'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE plans MODIFY currency CHAR(3) NOT NULL DEFAULT 'USD'");
    }
};
