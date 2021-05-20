<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Location;
use App\Models\Area;
use App\Models\DEOS_controller;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $buildings = Building::all();
        $locations = Location::all();

        foreach ($buildings as $item) {
            $item->areas;
            $item->controllers;
        }

        return view('admin.building.index', compact('buildings', 'locations'));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        Building::create($request->all());

        return back()->with('success', 'Created successfully');
    }

    public function show(Request $request, $id)
    {
        $building = Building::where('id', $id)->first();
        $controllers = $building->controllers;
        $areas = $building->areas;
        $locations = Location::all();

        return view('admin.building.details', compact('building', 'locations'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $result = Building::where('id', $id)->first();
        if (!$result) {
            return back()->with('error', 'Not found');
        }
        $result->update($request->all());

        return back()->with('success', 'Updated successfully');
    }

    public function destroy($id)
    {
        $building = Building::where('id', $id)->first();
        if (!$building) {
            return back()->with('error', 'Not found');
        }
        $building->delete();

        return redirect()->route('buildings')->with('success', 'Deleted successfully');
    }

    public function deleteAreas(Request $request, $id)
    {
        $items = [];
        foreach (json_decode($request->areaSelected) as $key => $val) {
            if ($val != true) continue;
            $items[] = $key;
        }
        Area::where('building_id', $id)->whereIn('id', $items)->delete();

        return back()->with('success', 'Deleted successfully');
    }

    public function deleteDeosControllers(Request $request, $id)
    {
        $items = [];
        foreach (json_decode($request->controllerSelected) as $key => $val) {
            if ($val != true) continue;
            $items[] = $key;
        }
        DEOS_controller::where('building_id', $id)->whereIn('id', $items)->delete();

        return back()->with('success', 'Deleted successfully');
    }
}
