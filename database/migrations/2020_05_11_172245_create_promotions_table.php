<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('commerce_id',36);
            $table->string('coupon')->unique();
            $table->integer('max');
            $table->integer('amount')->nullable();
            $table->date('expiry');
            $table->text('description');
            $table->string('image')->nullable();
            $table->float('discount')->nullable();;
            $table->timestamps();

            $table->foreign('commerce_id')->references('id')->on('commerces');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotions');
    }
}
