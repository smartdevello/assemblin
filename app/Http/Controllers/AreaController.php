<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Area;

class AreaController extends Controller
{
    public function index()
    {
        $buildings = Building::all();
        $areas = Area::all();
        foreach($areas as $area) {
            $area->building;
            $area->points;
        }
        foreach ($areas as $area) $area->building;

        return view('admin.area.index', compact('areas', 'buildings'));
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'building_id' => 'required'
        ]);

        if (! $this->checkAreaValid($request) )
        {
            $building = Building::where('id', $request->building_id) -> first();
            return back() -> with ('error', sprintf("The Building \"%s\" already has the area named \"%s\"", $building->name, $request->name));
        }
        Area::create(['name' => $request->name, 'building_id' => $request->building_id]);

        return back()->with('success', 'Created successfully');
    }

    public function checkAreaValid ( Request $request)
    {   
        $samebuildingAreas = Area::where('building_id', $request->building_id)->get();
        foreach ($samebuildingAreas as $area) {
            if ( $area->name == $request->name) return false;
        }
        return true;
    }

    public function show($id)
    {
        $area = Area::where('id', $id)->first();
        $buildings = Building::all();
        return view('admin.area.details', compact('area', 'buildings'));
    }

    public function update(Request $request, $id)
    {

        $area = Area::where('id', $id)->first();
        if (!$area) {
            return back()->with('error', 'Not found');
        }

        $this->validate($request, [
            'name' => 'required',
            'building_id' => 'required'
        ]);

        if ( $area->name != $request->name && !$this->checkAreaValid($request) )
        {
            $building = Building::where('id', $request->building_id) -> first();
            return back() -> with ('error', sprintf("The Building \"%s\" already has the area named \"%s\"", $building->name, $request->name));
        }

        $area->update($request->all());

        return back()->with('success', 'Updated successfully');
    }

    public function destroy($id)
    {
        $result = Area::where('id', $id)->first();
        if (!$result) {
            return back()->with('error', 'Not found');
        }
        $result->delete();

        return redirect()->route('areas')->with('success', 'Deleted successfully');
    }
}
