<?php

namespace App\Http\Controllers;

use App\Http\Traits\AssemblinInit;
use App\Http\Traits\WeatherForcastTrait;
use App\Imports\PointsImport;
use App\Models\Area;
use App\Models\Building;
use App\Models\DEOS_controller;
use App\Models\DEOS_point;
use App\Models\HKA_Scheduled_JOb;
use App\Models\Location;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Traits\ElectricityPriceForecastTrait;

class DEOS_controllerController extends Controller
{
    use AssemblinInit;
    use WeatherForcastTrait;
    use ElectricityPriceForecastTrait;
    public function index()
    {
        $buildings = Building::all();
        $controllers = DEOS_controller::all();
        foreach ($controllers as $item) {
            $item->building;
            $item->points;
        }
        $statement = DB::select("SHOW TABLE STATUS LIKE 'deos_controllers'");
        $nextId = $statement[0]->Auto_increment;
        return view('admin.controller.index', compact('controllers', 'buildings', 'nextId'));
    }

    public function checkControllerValid(Request $request)
    {
        $samebuildingcontrollers = DEOS_controller::where('building_id', $request->building_id)->get();
        foreach ($samebuildingcontrollers as $controller) {
            if ($controller->name == $request->name) {
                return false;
            }

        }
        return true;
    }
    public function create(Request $request)
    {

        $validate_rules = [
            'name' => 'required|unique:deos_controllers,name',
            'ip_address' => 'required',
            'port_number' => 'required',
            'building_id' => 'required',
        ];
        $validate_errors = [
            'name.required' => "Name field can't be empty",
            'name.unique' => sprintf("The Controller \"%s\" already exists", $request->name),
            'ip_address.required' => "IP Address can't be empty",
            'port_number.required' => "Port Number can't be empty",
            'building_id.required' => 'Controller must be belonged to a Building',
        ];

        if (! empty($request->longitude)) {
            $validate_rules['longitude'] = 'numeric|between:-180.00,180.00';
            $validate_errors['longitude.numeric'] = "Longitude should be numeric value";
            $validate_errors['longitude.between'] = "Longitude should be numeric value between -180 ~ 180";

        }
        if (! empty($request->latitude)) {
            $validate_rules['latitude'] = 'numeric|between:-90.00,90.00';
            $validate_errors['latitude.numeric'] = "Latitude should be numeric value";
            $validate_errors['latitude.between'] = "Latitude should be numeric value between -90 ~ 90";
        }

        $this->validate($request, $validate_rules, $validate_errors);

        $statement = DB::select("SHOW TABLE STATUS LIKE 'deos_controllers'");
        $nextId = $statement[0]->Auto_increment;
        $row = DEOS_controller::create([
            'name' => $request->name,
            'ip_address' => $request->ip_address,
            'port_number' => $nextId + 8000,
            'building_id' => $request->building_id,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
        ]);

        $this->stopAsmServices();
        $this->updateConfigfiles();
        $this->startAsmServices();
        return back()->with('success', 'Created successfully');
    }
    public function show($id)
    {
        $controller = DEOS_controller::where('id', $id)->first();

        $buildings = Building::all();
        foreach ($buildings as $building) {
            $building->location;
        }

        $points = $controller->points;
        $controllers = DEOS_controller::all();
        $areas = Area::all();
        return view('admin.controller.details', compact('controller', 'buildings', 'controllers', 'areas'));
    }

    public function update(Request $request, $id)
    {

        $controller = DEOS_controller::where('id', $id)->first();
        $building = $controller->building;
        $location = $building?->location;
        if (! $controller) {
            return back()->with('error', 'Not found');
        }

        $validate_rules = [
            'name' => 'required',
            'ip_address' => 'required',
            'building_id' => 'required',
        ];
        $validate_errors = [
            'name.required' => "Name field can't be empty",
            'ip_address.required' => "IP Address can't be empty",
            'building_id.required' => 'Controller must be belonged to a Building',
        ];
        if (! empty($request->longitude)) {
            $validate_rules['longitude'] = 'numeric|between:-180.00,180.00';
            $validate_errors['longitude.numeric'] = "Longitude should be numeric value";
            $validate_errors['longitude.between'] = "Longitude should be numeric value between -180 ~ 180";

        }
        if (! empty($request->latitude)) {
            $validate_rules['latitude'] = 'numeric|between:-90.00,90.00';
            $validate_errors['latitude.numeric'] = "Latitude should be numeric value";
            $validate_errors['latitude.between'] = "Latitude should be numeric value between -90 ~ 90";
        }

        $this->validate($request, $validate_rules, $validate_errors);

        // Disable all controllers to have weather_forecast functionalities
        // so only 1 controller to have weather functionality
        // Disable all controllers to have electricityprice_forcast functionalities
        // so only 1 controller to have electricityprice_forcast functionality

        // $controllers = DEOS_controller::all();
        // foreach ($controllers as $item) {
        //     $item->update([
        //         'enable_weather_forecast' => false,
        //         'enable_electricityprice_forecast' => false,
        //     ]);
        // }

        $controller->update([
            'name' => $request->name,
            'ip_address' => $request->ip_address,
            'port_number' => $request->port_number,
            'building_id' => $request->building_id,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'enable_weather_forecast' => isset($request->enable_weather_forecast) ? true : false,
            'enable_electricityprice_forecast' => isset($request->enable_electricityprice_forecast) ? true : false,
        ]);

        // Update scheduled job controller, so it can reference the current controller's coordinate
        if (isset($request->enable_weather_forecast)) {

            $job = HKA_Scheduled_JOb::updateOrCreate(
                ['job_name' => 'weather_forecast', 'job_id' => $controller->id], [
                    'job_name' => 'weather_forecast',
                    'job_id' => $controller->id,
                    'next_run' => date('Y-m-d H:i:s', time() + 60 * 60),
                ]
            );

            $forecast_data = $this->getWeatherData($request->longitude, $request->latitude);
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
                            ['label' => $label, 'name' => $name],
                            [
                                'name' => $name,
                                'label' => $label,
                                'type' => '',
                                'value' => $item['value'],
                                'controller_id' => $controller->id,
                                'meta_type' => 'weather_forecast',
                            ]);
                    }
                }

                $name = sprintf($location_name . '.f01.I%02d', $dataset_index + 109);
                $label = $key . '0';

                DEOS_point::updateOrCreate(
                    ['label' => $label, 'name' => $name],
                    [
                        'name' => $name,
                        'label' => $label,
                        'type' => '',
                        'value' => $data[0]['value'],
                        'controller_id' => $controller->id,
                        'meta_type' => 'weather_forecast',
                    ]);
                $dataset_index++;
            }
            $this->sendForcasttoDEOS('weather_forecast', $controller->id);

        }

        // Update scheduled job controller for electricity price forcast
        if (isset($request->enable_electricityprice_forecast)) {

            $job = HKA_Scheduled_JOb::updateOrCreate(
                ['job_name' => 'electricityprice_forecast'], array(
                    'job_name' => 'electricityprice_forecast',
                    'next_run' => date('Y-m-d H:i:s', time() + 5 * 60),
                    'job_id' => $controller->id,
                )
            );
            $point_data = $this->getElectricityPricePointData();
            foreach ($point_data as $data) {
                $label = $data['id'];
                $value = $data['value'];

                DEOS_point::updateOrCreate(
                    ['label' => $label, 'controller_id' => $controller->id],
                    [
                        'name' => $controller->name . ' ' . $label,
                        'label' => $label,
                        'type' => 'FL',
                        'meta_type' => 'electricityprice_forecast',
                        'value' => strval($value),
                        'controller_id' => $controller->id
                    ]
                );
            }

        }

        $this->stopAsmServices();
        $this->updateConfigfiles();
        $this->startAsmServices();
        return back()->with('success', 'Updated Successfully');
    }

    public function destroy($id)
    {
        $controller = DEOS_controller::where('id', $id)->first();
        if (! $controller) {
            return back()->with('error', 'Not found');
        }

        $filepath = config()->get('constants.BASE_CONFIG_PATH') . 'asmrest/' . $controller->name . ".json";

        $this->stopAsmServices();
        try {

            if (file_exists($filepath)) {
                unlink($filepath);
            }

        } catch (Exception $e) {

        }

        $controller->delete();

        $this->updateConfigfiles();
        $this->startAsmServices();
        return redirect()->route('controllers')->with('success', 'Deleted successfully');
    }

    public function createPoint(Request $request, $id)
    {
        $this->validate($request, ['name' => 'required', 'label' => 'required']);

        DEOS_point::create(['name' => $request->name, 'label' => $request->label, 'controller_id' => $id]);

        return back()->with('success', 'Created successfully');
    }

    public function deletePoints(Request $request, $id)
    {
        $items = [];
        foreach (json_decode($request->pointSelected) as $key => $val) {
            if ($val != true) {
                continue;
            }

            $items[] = $key;
        }
        DEOS_point::where('controller_id', $id)->whereIn('id', $items)->delete();

        return back()->with('success', 'Deleted successfully');
    }

    public function importPointsFromCsv(Request $request, $id)
    {

        try {

            if (! $request->file('file')) {
                return back()->with('error', 'Empty file');
            }

            $controller = DEOS_controller::where('id', $id)->first();
            $building = Building::where('id', $controller->building_id)->first();
            if (! $building) {
                return back()->with('error', 'This Controller does not belong to any Building');
            }

            $location = Location::where('id', $building->location_id)->first();
            if (! $location) {
                return back()->with('error', 'This Controller does not belong to any Location');
            }

            $rows = Excel::toCollection(new PointsImport, $request->file('file'));
            $this->stopAsmServices();
            foreach ($rows[0] as $index => $row) {
                //If Header continue;
                if ($index == 0) {
                    continue;
                }

                $data = [
                    'name' => sprintf("%s_%s_%s", $location->name, $building->name, $row[1]),
                    'label' => $row[2],
                    'type' => $row[4],
                    'meta_property' => $row[5],
                    'meta_room' => $row[6],
                    'meta_sensor' => $row[7],
                    'meta_type' => $row[8],
                    'value' => $row[3],
                    'controller_id' => $id,

                ];
                $point = DEOS_point::where('name', $data['name'])->first();

                if ($point == null) {
                    $point = DEOS_point::create($data);
                } else {
                    $point->update($data);
                }
            }
            $this->updateConfigfiles();
            $this->startAsmServices();
        } catch (Exception $e) {
            return back()->with('error', 'Something went wrong, Please import the same format of the exported file.');
        }
        return back()->with('success', 'Imported successfully');
    }

    public function exportPointsFromCsv(Request $request, $id)
    {
        $controller = DEOS_controller::where('id', $id)->first();
        $building = Building::where('id', $controller->building_id)->first();
        $location = Location::where('id', $building->location_id)->first();
        $points = DEOS_point::where('controller_id', $id)->get();

        $result = $points->map(function ($item) use ($location, $building) {

            if ($pos = strrpos($item->name, "_")) {
                $item->name = substr($item->name, $pos + 1);
            }
            return [$item->controller->name, $item->name, $item->label, $item->value, $item->type, $item->meta_property, $item->meta_room, $item->meta_sensor, $item->meta_type];
        });
        $result->prepend(['Controller Name', 'Point Name', 'Point Label', 'Value', 'Type', 'meta_property', 'meta_room', 'meta_sensor', 'meta_type']);
        $controller_name = $points[0]->controller->name;
        return $result->downloadExcel($controller_name . '-' . time() . '.csv');
    }
}