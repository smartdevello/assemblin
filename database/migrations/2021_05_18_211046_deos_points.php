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
            $table->string('name')->nullable();
            $table->string('label')->nullable();
            $table->string('type')->nullable();
            $table->string('meta_property')->nullable();
            $table->string('meta_room')->nullable();
            $table->string('meta_sensor')->nullable();
            $table->string('meta_type')->nullable();
            $table->string('value')->nullable();
            $table->unsignedBigInteger('controller_id')->nullable();
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
