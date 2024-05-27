<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Traits\SmallDataGarden;

class LongRunningJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SmallDataGarden;

    //Define properties for the params
    protected $job_name;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($job_name)
    {
        //
        $this->job_name = $job_name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // do something here for long running job
        if ($this->job_name == 'smalldatagarden') {            
            file_put_contents(storage_path('logs/smalldatagarden.log'), "SmallDataGarden Job is running\n", FILE_APPEND | LOCK_EX);
        }
        
    }
}
