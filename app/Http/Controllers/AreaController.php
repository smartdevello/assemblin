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
        foreach ($areas as $area) $area->building;

        return view('admin.area.index', compact('areas', 'buildings'));
    }

    public function create(Request $request)
    {
        $this->validate($request, ['name' => 'required', 'building_id' => 'required|exists:buildings,id']);

        Area::create(['name' => $request->name, 'building_id' => $request->building_id]);

        return back()->with('success', 'Created successfully');
    }

    public function show($id)
    {
        $area = Area::where('id', $id)->first();
        $buildings = Building::all();
        return view('admin.area.details', compact('area', 'buildings'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, ['building_id' => 'exists:buildings,id']);

        $result = Area::where('id', $id)->first();
        if (!$result) {
            return back()->with('error', 'Not found');
        }
        $result->update($request->all());

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
