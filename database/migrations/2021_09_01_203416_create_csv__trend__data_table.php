<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCsvTrendDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('csv_trend_data')) {
            Schema::create('csv_trend_data', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('trend_group_id');
                $table->foreign('trend_group_id')->references('id')->on('trend_groups')->onDelete('cascade');
                $table->timestamp('timestamp')->nullable();
                $table->string('sensor_name')->nullable();
                $table->float('sensor_value')->nullable();
                $table->timestamps();
            });
        } else
        Schema::table('csv_trend_data', function (Blueprint $table) {
            if (!Schema::hasColumn('csv_trend_data', 'id')) {
                $table->id();
                $table->unsignedBigInteger('trend_group_id');
                $table->foreign('trend_group_id')->references('id')->on('trend_groups')->onDelete('cascade');
                $table->timestamp('timestamp')->nullable();
                $table->string('sensor_name')->nullable();
                $table->float('sensor_value')->nullable();
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
        Schema::dropIfExists('csv_trend_data');
    }
}
