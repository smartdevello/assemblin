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
        Schema::create('deos_controllers', function (Blueprint $table) {
            $table->id();

            $table->text('name')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('port_number')->nullable();

            $table->unsignedBigInteger('building_id')->nullable();
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
        Schema::dropIfExists('deos_controllers');
    }
}
