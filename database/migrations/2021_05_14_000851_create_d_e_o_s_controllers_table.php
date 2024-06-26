<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDEOSControllersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('deos_controllers')) {
            Schema::create('deos_controllers', function (Blueprint $table) {
                $table->id();
                $table->text('name')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('port_number')->nullable();
                $table->double('longitude', 10, 4)->nullable();
                $table->double('latitude', 10, 4)->nullable();
                $table->boolean('enable_weather_forecast')->nullable();
                $table->boolean('enable_electricityprice_forecast')->nullable();

                $table->unsignedBigInteger('building_id')->nullable();
                $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
                $table->timestamps();
            });
        } else
        Schema::table('deos_controllers', function (Blueprint $table) {
            if (!Schema::hasColumn('deos_controllers', 'id')) {
                $table->id();
                $table->text('name')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('port_number')->nullable();
                $table->double('longitude', 10, 4)->nullable();
                $table->double('latitude', 10, 4)->nullable();
                $table->boolean('enable_weather_forecast')->nullable();
                $table->boolean('enable_electricityprice_forecast')->nullable();

                $table->unsignedBigInteger('building_id')->nullable();
                $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
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
        Schema::dropIfExists('deos_controllers');
    }
}