<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // this migration file for mysql only
    // because mysql doesn't support enum type with integer
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE short_urls MODIFY status INT NOT NULL DEFAULT 404");
        DB::statement("UPDATE short_urls SET status = CASE WHEN status = 1 THEN 200 WHEN status = 2 THEN 404 WHEN status = 3 THEN 498 ELSE status END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE short_urls MODIFY status ENUM('200', '404', '498') NOT NULL DEFAULT '404'");
    }
};
