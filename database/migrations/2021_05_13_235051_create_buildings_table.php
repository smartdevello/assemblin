<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('buildings')) {
            Schema::create('buildings', function (Blueprint $table) {
                $table->id();
                $table->text('name');
                $table->string('img_url')->nullable();
                $table->unsignedBigInteger('location_id')->nullable();
                $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
                $table->timestamps();
            });
        } else
            Schema::table('buildings', function (Blueprint $table) {
                if (!Schema::hasColumn('buildings', 'id')) {
                    $table->id();
                    $table->text('name');
                    $table->string('img_url')->nullable();
                    $table->unsignedBigInteger('location_id')->nullable();
                    $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
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
        Schema::dropIfExists('buildings');
    }
}
