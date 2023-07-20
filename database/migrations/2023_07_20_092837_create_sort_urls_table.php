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
        Schema::create('sort_urls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tld_id')->index()->nullable()->comment('tlds table id');
            $table->unsignedBigInteger('campaign_id')->index()->nullable()->comment('campaigns table id');
            $table->string('original_domain', 255)->unique()->index();
            $table->string('destination_domain', 255);
            $table->string('short_url', 255);
            $table->string('url_code', 255)->unique()->index();
            $table->string('tld', 255)->index()->nullable();
            $table->date('expired_date')->index();
            $table->boolean('auto_renewal')->default(false);
            $table->enum('status', [
                ShortUrlConstant::VALID,
                ShortUrlConstant::INVALID,
                ShortUrlConstant::EXPIRED,
            ])->index()->default(ShortUrlConstant::INVALID);
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('sort_urls');
    }
};
