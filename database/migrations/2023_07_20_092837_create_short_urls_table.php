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
        Schema::create('short_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')
                ->constrained('campaigns')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('original_domain', 255)->unique()->index();
            $table->string('destination_domain', 255);
            $table->string('short_url', 255);
            $table->string('tld_name')->index()->nullable();
            $table->string('tld_price')->nullable();
            $table->string('url_key', 255)->unique()->index();
            $table->date('expired_at')->index();
            $table->boolean('auto_renewal')->default(false);
            $table->enum('status', [
                ShortUrlConstant::VALID,
                ShortUrlConstant::INVALID,
                ShortUrlConstant::EXPIRED,
            ])->index()->default(ShortUrlConstant::INVALID);
            $table->text('remarks')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sort_urls');
    }
};
