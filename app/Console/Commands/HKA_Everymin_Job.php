<?php

namespace App\Console\Commands;

use App\Models\HKA_Scheduled_JOb;
use App\Models\TrendGroup;
use Illuminate\Console\Command;
use App\Http\Traits\TrendDataTrait;

class HKA_Everymin_Job extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    use TrendDataTrait;
    protected $signature = 'hka_job:everymin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Respectively execute jobs in hka_scheduled_jobs table every minute.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // file_put_contents('cron.txt', "run it once\n", FILE_APPEND | LOCK_EX);
        $all_jobs = HKA_Scheduled_JOb::all();
        foreach($all_jobs as $job){
            $next_run = strtotime( $job->next_run);
            if ( time() < $next_run) {
                if ($job->job_name == 'trend_group') {
                    $trend_group = TrendGroup::where('id', $job->job_id)->first();
                    if ($trend_group ) {
                        file_put_contents('cron.txt', "run it once\n", FILE_APPEND | LOCK_EX);
                        $job->update([
                            'next_run' => date('Y-m-d H:i:s', time() + $trend_group->update_interval * 60)
                        ]);
                        // $this->receive_csv_save_db($trend_group);
                    } else {
                        $job->delete();
                    }
                }
                
            }
        }
        $this->info('Successfully run.');
    }
}
