<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOfflineMethodLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offline_method_languages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('offline_method_id')->unsigned();
            $table->string('lang',10)->default('en')->index();
            $table->string('name', 100)->comment('name of payment method');
            $table->text('instructions')->nullable('instructions to follow for paying');
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
        Schema::dropIfExists('offline_method_languages');
    }
}
