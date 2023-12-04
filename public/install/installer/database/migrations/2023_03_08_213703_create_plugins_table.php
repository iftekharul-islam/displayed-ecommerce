<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePluginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->nullable();
            $table->string('author')->nullable();
            $table->string('author_url')->nullable();
            $table->text('image')->nullable();
            $table->string('description')->nullable();
            $table->string('tags')->nullable();
            $table->string('plugin_identifier');
            $table->string('directory');
            $table->string('purchase_code')->nullable();
            $table->string('version');
            $table->string('required_cms_version');
            $table->boolean('status')->default(0);
            $table->string('license')->nullable();
            $table->string('license_url')->nullable();
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
        Schema::dropIfExists('plugins');
    }
}
