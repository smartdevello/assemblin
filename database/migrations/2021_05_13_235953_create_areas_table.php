<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('areas')) {
            Schema::create('areas', function (Blueprint $table) {
                $table->id();
                $table->text('name');
                $table->unsignedBigInteger('building_id')->nullable();
                $table->foreign('building_id')->references('id')->on('buildings')->onDelete('cascade');
                $table->timestamps();
            });
        } else
        Schema::table('areas', function (Blueprint $table) {
            if (!Schema::hasColumn('areas', 'id')) {
                $table->id();
                $table->text('name');
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
        Schema::dropIfExists('areas');
    }
}
