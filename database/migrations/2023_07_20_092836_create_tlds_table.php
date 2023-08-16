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
        Schema::create('tlds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')
                ->constrained('campaigns')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('name', 255)->index();
            $table->string('price', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->comment('from users table');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('from users table');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('from users table');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tlds');
    }
};
