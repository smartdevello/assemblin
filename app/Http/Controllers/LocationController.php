<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Building;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::all();
        foreach ($locations as $location) {
            $buildings = Building::where('location_id', $location->id)->get();
            $location->buildings = $buildings;
        }
        return view('admin.location.index', compact('locations'));
    }

    public function create(Request $request)
    {
        $this->validate($request, ['name' => 'required']);
        Location::create(['name' => $request->name]);
        return back()->with('success', 'Created successfully');
    }

    public function show(Request $request, $id)
    {
        $location = Location::where('id', $id)->first();
        $buildings = $location->buildings;

        return view('admin.location.details', compact('location', 'buildings'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, ['name' => 'required']);
        $result = Location::where('id', $id)->first();
        if (!$result) {
            return back()->with('error', 'Not found');
        }
        $result->update(['name' => $request->name]);

        return back()->with('success', 'Updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $result = Location::where('id', $id)->first();
        if (!$result) {
            return back()->with('error', 'Not found');
        }
        $result->delete();

        return redirect()->route('locations')->with('success', 'Deleted successfully');
    }

    public function delete_buildings(Request $request, $id)
    {
        $buildings = [];
        foreach (json_decode($request->buildingSelected) as $key => $val) {
            if ($val != true) continue;
            $buildings[] = $key;
        }
        Building::where('location_id', $id)->whereIn('id', $buildings)->delete();

        return back()->with('success', 'Deleted successfully');
    }
}
