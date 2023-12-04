<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VideoShoppings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_shoppings', function (Blueprint $table) {
            $table->id();
            $table->string('slug',100)->index();
            $table->bigInteger('user_id')->default(1);
            $table->bigInteger('thumbnail_id')->nullable();
            $table->text('thumbnail')->nullable();
            $table->string('style',100)->default('style_1');
            $table->string('video_type',100)->nullable();
            $table->text('video_url')->nullable();
            $table->tinyInteger('is_live')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('enable_related_product')->default(1);
            $table->string('product_ids')->nullable();
            $table->bigInteger('total_viewed')->default(0);
            $table->timestamps();
        });

        Schema::create('video_shopping_languages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('video_shopping_id')->index();
            $table->string('lang',10)->index()->comment('our default locale for system en');
            $table->string('title')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_shoppings');
        Schema::dropIfExists('video_shopping_languages');
    }
}
