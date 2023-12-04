<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfflineMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offline_methods', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->default('custom_payment');
            $table->bigInteger('thumbnail_id')->unsigned()->nullable();
            $table->text('thumbnail')->nullable();
            $table->text('bank_details')->nullable();
            $table->tinyInteger('status')->default(1);
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
        Schema::dropIfExists('offline_methods');
    }
}
