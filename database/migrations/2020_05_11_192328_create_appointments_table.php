<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('client_id',36);
            $table->string('commerce_id',36);
            $table->bigInteger('service_id')->unsigned();
            $table->date('schedule_day');
            $table->time('schedule_hour');
            $table->string('status');
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('commerce_id')->references('id')->on('commerces');
            $table->foreign('service_id')->references('id')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}
