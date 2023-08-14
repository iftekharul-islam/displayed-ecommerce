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
        Schema::create('visitor_count_by_cities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visitor_count_by_country_id')->index()->comment('visitor_count_by_countries table id');
            $table->foreign('visitor_count_by_country_id')->references('id')->on('visitor_count_by_countries')->onDelete('cascade');
            $table->string('city')->index()->nullable();
            $table->date('visit_date')->index();
            $table->integer('total_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_count_by_cities');
    }
};
