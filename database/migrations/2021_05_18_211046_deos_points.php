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
<<<<<<< HEAD:database/migrations/2021_05_18_170303_create_deos_points_table.php

            $table->string('name');
            $table->string('value');

=======
            $table->string('name')->nullable();
            $table->string('value')->nullable();
            $table->unsignedBigInteger('controller_id')->nullable();
            $table->foreign('controller_id')->references('id')->on('deos_controllers')->onDelete('cascade');
>>>>>>> 9aab86b90ebd6cd096411f222c3365ac338dc8df:database/migrations/2021_05_18_211046_deos_points.php
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
