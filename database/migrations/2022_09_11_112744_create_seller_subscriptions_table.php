<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seller_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('seller_package_id');
            $table->string('payment_method');
            $table->unsignedBigInteger('offline_method_id')->nullable();
            $table->text('offline_method_file')->nullable();
            $table->double('amount')->default(0);
            $table->string('trx_id');
            $table->integer('product_upload_limit')->default(0);
            $table->dateTime('purchase_at');
            $table->dateTime('expires_at');
            $table->text('payment_details')->nullable();
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('seller_subscriptions');
    }
}
