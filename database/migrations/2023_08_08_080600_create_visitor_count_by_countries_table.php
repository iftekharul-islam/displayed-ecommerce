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
        Schema::create('visitor_count_by_countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_url_id')
                ->constrained('short_urls')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('country')->index()->nullable();
            $table->date('visited_at')->index();
            $table->integer('total_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_count_by_countries');
    }
};