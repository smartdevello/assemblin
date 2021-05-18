<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeosPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deos_points', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('sensor');

            $table->foreignId('controller_id');
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
        Schema::dropIfExists('deos_points');
    }
}
