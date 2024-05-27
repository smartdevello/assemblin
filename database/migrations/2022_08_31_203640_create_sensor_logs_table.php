<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSensorLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('sensor_logs')) {
            Schema::create('sensor_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sensor_id');
                $table->foreign('sensor_id')->references('id')->on('sensors')->onDelete('cascade');
                $table->longText('logs')->nullable();
                $table->timestamps();
            });
        } else
        Schema::table('sensor_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('sensor_logs', 'id')) {
                $table->id();
                $table->unsignedBigInteger('sensor_id');
                $table->foreign('sensor_id')->references('id')->on('sensors')->onDelete('cascade');
                $table->longText('logs')->nullable();
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sensor_logs');
    }
}
