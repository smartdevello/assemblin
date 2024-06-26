<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table) {
                $table->id();
                $table->text('name');
                $table->string('img_url')->nullable();
                $table->timestamps();
            });
        } else
        Schema::table('locations', function (Blueprint $table) {
            if (!Schema::hasColumn('locations', 'id')) {
                $table->id();
                $table->text('name');
                $table->string('img_url')->nullable();
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
        Schema::dropIfExists('locations');
    }
}
