<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seller_packages', function (Blueprint $table) {
            $table->id();
            $table->double('price')->default(0);
            $table->boolean('is_free')->default(0);
            $table->integer('product_upload_limit')->default(0);
            $table->integer('duration')->default(0);
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('image_id')->nullable();
            $table->text('image')->nullable();
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
        Schema::dropIfExists('seller_packages');
    }
}
