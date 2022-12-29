<?php

namespace App\Console\Commands;

use App\Http\Traits\AssemblinInit;
use App\Http\Traits\ElectricyPriceForcastTrait;
use App\Http\Traits\SmallDataGarden;
use App\Http\Traits\TrendDataTrait;
use App\Http\Traits\WeatherForcastTrait;
use App\Models\DEOS_controller;
use App\Models\DEOS_point;
use App\Models\HKA_Scheduled_JOb;
use App\Models\TrendGroup;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;

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
    use ElectricyPriceForcastTrait;
    use SmallDataGarden;

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
        foreach ($all_jobs as $job) {
            $next_run = strtotime($job->next_run);
            if (time() > $next_run) {
                if ($job->job_name == 'trend_group') {
                    try {
                        $trend_group = TrendGroup::where('id', $job->job_id)->first();
                        if ($trend_group) {
                            $job->update([
                                'next_run' => date('Y-m-d H:i:s', time() + $trend_group->update_interval * 60),
                            ]);
                            if ($trend_group->send_to_ftp == false) {
                                $this->receive_csv_save_db($trend_group);
                            } else {
                                $this->receive_csv_and_savefile_sendto_external_ftp($trend_group);
                            }
                        } else {
                            $job->delete();
                        }
                    } catch (\Exception $e) {

                    }

                } else if ($job->job_name == 'weather_forecast') {
                    //Check if the controller exists for weather_forcast
                    $controller = DEOS_controller::where('id', $job->job_id)->first();
                    if ($controller && isset($controller->longitude) && isset($controller->latitude)) {
                        // Update Job next schedule time
                        $job->update([
                            'next_run' => date('Y-m-d H:i:s', time() + 600),
                        ]);

                        //perform relevant actions

                        $forecast_data = $this->getWeatherData($controller->longitude, $controller->latitude);
                        //Create or Update Weather Points (Actually DEOS Points)
                        $dataset_index = 0;
                        foreach ($forecast_data as $key => $data) {
                            foreach ($data as $index => $item) {
                                //Skip first or last data among 50 , so we need only middle 48 data
                                if ($index == 0 || $index == 49) {
                                    continue;
                                }

                                if ($dataset_index == 0) {
                                    $label = sprintf('fmi.f:I%02d', $index);
                                } else {
                                    $label = sprintf('fmi.f:I%03d', $index + $dataset_index * 100);
                                }

                                $point = DEOS_point::where([
                                    ['name', '=', $key . $index],
                                    ['label', '=', $label],
                                    ['controller_id', '=', $controller->id],
                                    ['meta_type', '=', 'weather_forcast'],
                                ])->first();

                                if ($point != null) {

                                    $point->update([
                                        'name' => $key . $index,
                                        'label' => $label,
                                        'type' => 'FL',
                                        'value' => $item['value'],
                                        'controller_id' => $controller->id,
                                        'meta_type' => 'weather_forcast',
                                    ]);

                                } else {

                                    DEOS_point::create([
                                        'name' => $key . $index,
                                        'label' => $label,
                                        'type' => 'FL',
                                        'value' => $item['value'],
                                        'controller_id' => $controller->id,
                                        'meta_type' => 'weather_forcast',
                                    ]);

                                }

                            }
                            $dataset_index++;
                        }
                        $this->sendForcasttoDEOS('weather_forcast', $controller->id);
                    } else {
                        $job->delete();
                    }
                } else if ($job->job_name == "electricityprice_forecast") {
                    $controller = DEOS_controller::where('id', $job->job_id)->first();
                    if ($controller) {
                        $forecast_data = $this->getElectricityPriceData();
                        $date = new DateTime();
                        $date->modify('+1 hours');
                        $date->setTimezone(new DateTimeZone('Europe/Helsinki'));
                        $date->setTime($date->format("H"), 0, 0);
                        $date->getTimestamp();

                        for ($index = 1; $index <= 25; $index++) {

                            $label = sprintf('fmi.f:I%02d', $index);

                        }
                    } else {
                        $job->delete();
                    }
                } else if ($job->job_name == "automatic_update") {

                    $job->update([
                        'next_run' => date('Y-m-d H:i:s', time() + 5 * 60),
                    ]);
                    $this->automatic_update();
                } else if ($job->job_name == 'smalldatagarden') {
                    $job->update([
                        'next_run' => date('Y-m-d H:i:s', time() + 30 * 60),
                    ]);
                    $this->SmallDataGarden_updateSensors();
                }

            }
        }
        $this->info('Successfully run.');
    }
}