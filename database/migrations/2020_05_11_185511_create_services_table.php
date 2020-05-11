<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('comerce_id')->unsigned();
            $table->bigInteger('category_id')->unsigned();
            $table->string('name');
            $table->mediumText('description');
            $table->double('price', 8, 2);
            $table->timestamps();

            $table->foreign('comerce_id')->references('id')->on('commerces');
            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
    }
}
