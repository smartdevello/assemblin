<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Sensors_Copy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('sensors_copy', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('observationId')->nullable();
            $table->string('deviceId')->nullable();
            $table->string('tag')->nullable();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('unit')->nullable();
            $table->float('value')->nullable();
            $table->string('message_time')->nullable();

            $table->unsignedBigInteger('point_id')->nullable();
            $table->foreign('point_id')->references('id')->on('deos_points')->onDelete('cascade');
            $table->string('point_name')->nullable();
            $table->boolean('visibility')->default(true);
            $table->string('logs')->nullable();
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
        Schema::dropIfExists('sensors_copy');
    }
}
