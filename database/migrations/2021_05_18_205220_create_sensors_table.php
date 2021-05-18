<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSensorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sensors', function (Blueprint $table) {
            $table->id();

            $table->string('sensorId')->nullable()->default(null);
            $table->string('deviceId')->nullable()->default(null);
            $table->string('tag')->nullable()->default(null);
            $table->string('name')->nullable()->default(null);
            $table->string('type')->nullable()->default(null);
            $table->string('unit')->nullable()->default(null);
            $table->float('value')->nullable()->default(null);
            $table->string('message_time')->nullable()->default(null);
            $table->string('DEOS_pointId')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sensors');
    }
}
