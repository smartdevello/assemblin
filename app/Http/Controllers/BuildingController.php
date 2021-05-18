<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Location;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        //
        $buildings = Building::all();
        $locations = Location::all();
        return view('admin.building.index', compact('buildings', 'locations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        $this->validate($request, 
            [
                'name' => 'required',
                'location_id' => 'required'
            ]
        );

        Building::create(
            [
                'name' => $request->name,
                'location_id' => $request->location_id
            ]
        );

        return back()->with('success', 'Created successfully');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function show(Request $request, $id)
    {
        $building = Building::where('id', $id)->first();
        $locations = Location::all();
        return view('admin.building.details', compact('building', 'locations'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, 
            [
                'name' => 'required',
                'location_id' => 'required',
            ]
        );
        $result = Building::where('id', $id)->first();
        if (!$result) {
            return back()->with('error', 'Not found');
        }
        $result->update(
            [
                'name' => $request->name,
                'location_id' => $request->location_id,
            ]
        );

        return back()->with('success', 'Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $result = Building::where('id', $id)->first();
        if (!$result) {
            return back()->with('error', 'Not found');
        }
        $result->delete();

        return redirect()->route('building')->with('success', 'Deleted successfully');
    }
}
