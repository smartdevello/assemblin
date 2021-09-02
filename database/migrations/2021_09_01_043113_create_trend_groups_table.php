<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrendGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('trend_groups', function (Blueprint $table) {
            $table->id();
            $table->string('controller_id')->nullable();
            $table->string('trend_group_name')->nullable();
            $table->string('location_name')->nullable();
            $table->unsignedBigInteger('update_interval')->nullable();
            $table->bigInteger('query_period')->nullable();
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
        Schema::dropIfExists('trend_groups');
    }
}
