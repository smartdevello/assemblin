<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeosPoints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('deos_points', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('value');
<<<<<<< HEAD:database/migrations/2021_05_18_211046_deos_points.php
            $table->unsignedBigInteger('controller_id')->nullable();
=======

            $table->foreignId('controller_id');
>>>>>>> 184e4496f9e67c80282628b2db0e1c2791cdeca1:database/migrations/2021_05_18_170303_create_deos_points_table.php
            $table->foreign('controller_id')->references('id')->on('deos_controllers')->onDelete('cascade');
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
        Schema::dropIfExists('deos_points');
    }
}
