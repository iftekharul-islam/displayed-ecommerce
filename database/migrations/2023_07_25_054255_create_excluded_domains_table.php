<?php

use App\Constants\ShortUrlConstant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('excluded_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')
                ->constrained('campaigns')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('domain', 255)->unique()->index();
            $table->date('expired_at')->index();
            $table->boolean('auto_renewal')->default(false);
            $table->enum('status', [
                ShortUrlConstant::VALID,
                ShortUrlConstant::INVALID,
                ShortUrlConstant::EXPIRED,
            ])->index()->default(ShortUrlConstant::VALID);
            $table->string('note', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->comment('from users table');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('from users table');
            $table->unsignedBigInteger('deleted_by')->nullable()->comment('from users table');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excluded_domains');
    }
};
