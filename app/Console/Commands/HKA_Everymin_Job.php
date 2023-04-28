<?php

namespace App\Console\Commands;

use App\Http\Traits\AssemblinInit;
use App\Http\Traits\ElectricityPriceForecastTrait;
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
    use ElectricityPriceForecastTrait;
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
                    //Check if the controller exists for weather_forecast
                    $controller = DEOS_controller::where('id', $job->job_id)->first();
                    if ($controller && isset($controller->longitude) && isset($controller->latitude)) {
                        // Update Job next schedule time
                        $job->update([
                            'next_run' => date('Y-m-d H:i:s', time() + 5 * 60),
                        ]);

                        //perform relevant actions

                        $forecast_data = $this->getWeatherData($controller->longitude, $controller->latitude);
                        $building = $controller->building;
                        $location = $building?->location;

                        //Create or Update Weather Points (Actually DEOS Points)
                        $dataset_index = 0;
                        $pointIndex = 0;
                        $location_name = $location?->name ?? "";
                        foreach ($forecast_data as $key => $data) {
                            foreach ($data as $index => $item) {

                                if (strpos($key, 'temperature') !== false || strpos($key, 'PrecipitationAmount') !== false || strpos($key, 'windspeedms') !== false) {
                                    //break if $index == 36, because we need only first 36
                                    if ($index == 36)
                                        break;
                                    // saalahti . f01 . I01->saalahti . f101 . I108 .
                                    $name = sprintf($location_name . '.f01.I%02d', $index + 1 + $dataset_index * 36);
                                    $label = $key . $index;

                                    DEOS_point::updateOrCreate(
                                        ['label' => $name, 'name' => $name],
                                        [
                                            'name' => $name,
                                            'label' => $name,
                                            'type' => 'FL',
                                            'value' => $item['value'],
                                            'controller_id' => $controller->id,
                                            'meta_type' => 'weather_forecast',
                                        ]);
                                }
                            }

                            $name = sprintf($location_name . '.f01.I%02d', $dataset_index + 109);
                            $label = $key . '0';

                            DEOS_point::updateOrCreate(
                                ['label' => $name, 'name' => $name],
                                [
                                    'name' => $name,
                                    'label' => $name,
                                    'type' => 'FL',
                                    'value' => $data[0]['value'],
                                    'controller_id' => $controller->id,
                                    'meta_type' => 'weather_forecast',
                                ]);
                            $dataset_index++;
                        }
                        $this->sendForcasttoDEOS('weather_forecast', $controller->id);

                    } else {
                        $job->delete();
                    }
                } else if ($job->job_name == "electricityprice_forecast") {
                    $job->update([
                        'next_run' => date('Y-m-d H:i:s', time() + 5 * 60),
                    ]);
                    $controller = DEOS_controller::where('id', $job->job_id)->first();

                    if ($controller) {
                        $point_data = $this->getElectricityPricePointData();
                        $building = $controller->building;
                        $location = $building?->location;
                        $location_name = $location?->name ?? "";
                        foreach ($point_data as $data) {
                            $label = $data['id'];
                            $name = $location_name . '.' . $label;
                            $value = $data['value'];

                            DEOS_point::updateOrCreate(
                                ['label' => $name, 'name' => $name, 'controller_id' => $controller->id],
                                [
                                    'name' => $name,
                                    'label' => $name,
                                    'type' => 'FL',
                                    'meta_type' => 'electricityprice_forecast',
                                    'value' => strval($value),
                                    'controller_id' => $controller->id
                                ]
                            );
                        }
                        $this->sendForcasttoDEOS('electricityprice_forecast', $controller->id);
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