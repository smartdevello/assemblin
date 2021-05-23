<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Building;
use Illuminate\Support\Facades\File;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::all();
        foreach ($locations as $location) {
            if ($location->img_url)  {
                $location->img_url = asset('images/' . $location->img_url);
            }

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
        if ($location->img_url)  {
            $location->img_url = asset('images/' . $location->img_url);
        }
        $buildings = $location->buildings;

        return view('admin.location.details', compact('location', 'buildings'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        $location = Location::where('id', $id)->first();
        if (!$location) {
            return back()->with('error', 'Not found');
        }
        $imageName = "";
        if ($request->image) {

            //update image
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg',
            ]);
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            
            //Remove previous image
            if (!empty( $imageName ) && !empty( $location->img_url)) {

                if (File::exists(public_path('images/' . $location->img_url))) {
                    File::delete( public_path('images/' . $location->img_url) );
                }
            }
        }
        $location->update([
            'name' => $request->name,
            'img_url' => $request->image ? $imageName: null,
        ]);

        return back()->with('success', 'Updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $location = Location::where('id', $id)->first();
        if (!$location) {
            return back()->with('error', 'Not found');
        }

        //Delete Image file
        if ( !empty($location->img_url)) {
            if (File::exists(public_path('images/' . $location->img_url))) {
                File::delete( public_path('images/' . $location->img_url) );
            }
        }
        
        $location->delete();

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
