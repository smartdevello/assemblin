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
        if (!Schema::hasTable('trend_groups')) {
            Schema::create('trend_groups', function (Blueprint $table) {
                $table->id();
                $table->string('controller_id')->nullable();
                $table->string('trend_group_name')->nullable();
                $table->string('location_name')->nullable();
                $table->bigInteger('update_interval')->nullable();
                $table->bigInteger('query_period')->nullable();
                $table->boolean('send_to_ftp')->nullable();
                $table->timestamps();
            });
        } else
        Schema::table('trend_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('trend_groups', 'id')) {
                $table->id();
                $table->string('controller_id')->nullable();
                $table->string('trend_group_name')->nullable();
                $table->string('location_name')->nullable();
                $table->bigInteger('update_interval')->nullable();
                $table->bigInteger('query_period')->nullable();
                $table->boolean('send_to_ftp')->nullable();
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
        Schema::dropIfExists('trend_groups');
    }
}
