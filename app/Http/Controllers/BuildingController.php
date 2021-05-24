<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Location;
use App\Models\Area;
use App\Models\DEOS_controller;
use Illuminate\Support\Facades\File;

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

        foreach ($buildings as $building) {
            $building->areas;
            $building->controllers;

            if ($building->img_url)  {
                $building->img_url = asset('images/' . $building->img_url);
            }
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

        if ($building->img_url)  {
            $building->img_url = asset('images/' . $building->img_url);
        }

        return view('admin.building.details', compact('building', 'locations'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $building = Building::where('id', $id)->first();
        if (!$building) {
            return back()->with('error', 'Not found');
        }

        $imageName = "";

        if ($request->image) {            
            //update image
            $request->validate([
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            
            //Remove previous image
            if (!empty( $imageName ) && !empty( $building->img_url)) {

                if (File::exists(public_path('images/' . $building->img_url))) {
                    File::delete( public_path('images/' . $building->img_url) );
                }
            }
        }
        $building->update([
            'name' => $request->name,
            'location_id' => $request->location_id ? $request->location_id : null,
            'img_url' => $request->image ? $imageName: null,
        ]);

        return back()->with('success', 'Updated successfully');
    }

    public function destroy($id)
    {
        $building = Building::where('id', $id)->first();
        if (!$building) {
            return back()->with('error', 'Not found');
        }

        //Delete Image file
        if ( !empty($building->img_url)) {
            if (File::exists(public_path('images/' . $building->img_url))) {
                File::delete( public_path('images/' . $building->img_url) );
            }
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
