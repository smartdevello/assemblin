<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Sensors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('sensors', function (Blueprint $table) {
            $table->id();

            $table->string('sensorId');
            $table->string('deviceId');
            $table->string('tag');
            $table->string('name');
            $table->string('type');
            $table->string('unit');
            $table->float('value');
            $table->dateTime('message_time');
            
            $table->unsignedBigInteger('deos_pointId');
            $table->foreign('deos_pointId')->references('id')->on('deos_points')->onDelete('cascade');
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
        //
        Schema::dropIfExists('sensors');
    }
}
