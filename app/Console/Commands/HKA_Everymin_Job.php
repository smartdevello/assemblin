<?php

namespace App\Console\Commands;

use App\Models\HKA_Scheduled_JOb;
use App\Models\TrendGroup;
use Illuminate\Console\Command;
use App\Http\Traits\TrendDataTrait;
use App\Http\Traits\WeatherForcastTrait;
use App\Http\Traits\AssemblinInit;

use App\Models\DEOS_controller;
use App\Models\DEOS_point;

class HKA_Everymin_Job extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    use TrendDataTrait;
    use AssemblinInit;
    use WeatherForcastTrait;

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
            if ( time() > $next_run) {
                if ($job->job_name == 'trend_group') {
                    $trend_group = TrendGroup::where('id', $job->job_id)->first();
                    if ($trend_group ) {
                        $job->update([
                            'next_run' => date('Y-m-d H:i:s', time() + $trend_group->update_interval * 60)
                        ]);
                        $this->receive_csv_save_db($trend_group);
                    } else {
                        $job->delete();
                    }
                } else if ( $job->job_name == 'weather_forecast') {
                    //Check if the controller exists for weather_forcast
                    $controller = DEOS_controller::where('id', $job->job_id)->first();
                    if ($controller && isset($controller->longitude) && isset($controller->latitude)) {
                        // Update Job next schedule time
                        $job->update([
                            'next_run' => date('Y-m-d H:i:s', time() + 3600)
                        ]);

                        //perform relevant actions

                        $forecast_data = $this->getWeatherData($controller->longitude, $controller->latitude);
                        //Create or Update Weather Points (Actually DEOS Points)
                        $dataset_index = 0;
                        foreach ($forecast_data as $key => $data)
                        {
                            foreach($data as $index => $item){
                                //Skip first or last data among 50 , so we need only middle 48 data
                                if ($index == 0 || $index == 49) continue;

                                $label = sprintf('fmi.f:I%03d', $index + $dataset_index * 100);
                                                                
                                $point = DEOS_point::where([
                                    ['name', '=' , $key . $index],
                                    ['label', '=', $label]
                                ])->first();

                                if ($point !=null) {

                                    $point->update([
                                        'name' => $key . $index,
                                        'label' => $label, 
                                        'type' => 'FL',
                                        'value' => $item['value'],
                                        'controller_id' => $controller->id,
                                        'meta_type' => 'weather_forcast'                                    
                                    ]);

                                } else {

                                    DEOS_point::create([
                                        'name' => $key . $index,
                                        'label' => $label, 
                                        'type' => 'FL',
                                        'value' => $item['value'],
                                        'controller_id' => $controller->id,
                                        'meta_type' => 'weather_forcast'                                    
                                    ]);

                                }

                            }
                            $dataset_index++;
                        }
                    } else {
                        $job->delete();
                    }
                } else if ($job->job_name  == "automatic_update"){

                    $job->update([
                        'next_run' => date('Y-m-d H:i:s', time() + 5 * 60)
                    ]);
                    $this->automatic_update();
                }
                
            }
        }
        $this->info('Successfully run.');
    }
}
