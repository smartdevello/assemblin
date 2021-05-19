<?php

namespace App\Http\Controllers;

use App\Imports\DEOS_pointImport;
use App\Imports\DeosPointImport;
use Illuminate\Http\Request;
use App\Models\DEOS_controller;
use App\Models\Building;
use App\Models\DEOS_point;
use Maatwebsite\Excel\Facades\Excel;

class DEOS_controllerController extends Controller
{
    public function index()
    {
        $buildings = Building::all();
        $controllers = DEOS_controller::all();
        foreach ($controllers as $item) $item->building;

        return view('admin.controller.index', compact('controllers', 'buildings'));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'ip_address' => 'required',
            'port_number' => 'required',
            'building_id' => 'required|exists:buildings,id'
        ]);
        DEOS_controller::create($request->all());

        return back()->with('success', 'Created successfully');
    }

    public function show($id)
    {
        $controller = DEOS_controller::where('id', $id)->first();
        $buildings = Building::all();
        $points = $controller->points;

        return view('admin.controller.details', compact('controller', 'buildings'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, ['building_id' => 'exists:buildings,id']);

        $result = DEOS_controller::where('id', $id)->first();
        if (!$result) {
            return back()->with('error', 'Not found');
        }
        $result->update($request->all());

        return back()->with('success', 'Updated successfully');
    }

    public function destroy($id)
    {
        $result = DEOS_controller::where('id', $id)->first();
        if (!$result) {
            return back()->with('error', 'Not found');
        }
        $result->delete();

        return redirect()->route('controllers')->with('success', 'Deleted successfully');
    }

    public function createPoint(Request $request, $id)
    {
        $this->validate($request, ['name' => 'required', 'value' => 'required']);
        DEOS_point::create(['name' => $request->name, 'value' => $request->value, 'controller_id' => $id]);

        return back()->with('success', 'Created successfully');
    }

    public function deletePoints(Request $request, $id)
    {
        $items = [];
        foreach (json_decode($request->pointSelected) as $key => $val) {
            if ($val != true) continue;
            $items[] = $key;
        }
        DEOS_point::where('controller_id', $id)->whereIn('id', $items)->delete();

        return back()->with('success', 'Deleted successfully');
    }

    public function importPointsFromCsv(Request $request, $id)
    {
        if (!$request->file('file')) {
            return back()->with('error', 'Empty file');
        }
        $rows = Excel::toCollection(new DeosPointImport, $request->file('file'));
        foreach ($rows[0] as $row) {
            DEOS_point::create(['name' => $row[0], 'value' => $row[1], 'controller_id' => $id]);
        }
        return back()->with('success', 'Imported successfully');
    }

    public function exportPointsFromCsv(Request $request, $id)
    {
        $points = DEOS_point::where('controller_id', $id)->get();
        $result = $points->map(function ($item) {
            return [$item->name, $item->value, $item->controller->name];
        });
        $result->prepend(['Name', 'Value', 'Controller Name']);

        return $result->downloadExcel('points.xlsx');
    }
}
