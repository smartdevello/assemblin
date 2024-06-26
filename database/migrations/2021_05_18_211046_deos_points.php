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
        if (!Schema::hasTable('deos_points')) {
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
                $table->foreign('controller_id')->references('id')->on('deos_controllers')->onDelete('cascade');

                $table->unsignedBigInteger('area_id')->nullable();
                $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');

                $table->timestamps();
            });
        } else
        Schema::table('deos_points', function (Blueprint $table) {
            if (!Schema::hasColumn('deos_points', 'id')) {
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
                $table->foreign('controller_id')->references('id')->on('deos_controllers')->onDelete('cascade');

                $table->unsignedBigInteger('area_id')->nullable();
                $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');

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
        //
        Schema::dropIfExists('deos_points');
    }
}
