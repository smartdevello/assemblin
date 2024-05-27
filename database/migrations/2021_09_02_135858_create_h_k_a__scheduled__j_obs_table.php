<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHKAScheduledJObsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('hka_scheduled_jobs')) {
            Schema::create('hka_scheduled_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('job_name');
                $table->unsignedBigInteger('job_id');
                $table->timestamp('next_run');
                $table->timestamps();
            });
        } else
        Schema::table('hka_scheduled_jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('hka_scheduled_jobs', 'id')) {
                $table->id();
                $table->string('job_name');
                $table->unsignedBigInteger('job_id');
                $table->timestamp('next_run');
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
        Schema::dropIfExists('hka_scheduled_jobs');
    }
}
