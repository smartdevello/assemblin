<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Building;
use App\Models\Area;

class AreaController extends Controller
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
        $areas = area::all();
        return view('admin.area.index', compact('areas', 'buildings'));
        
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
                'building_id' => 'required'
            ]
        );

        Area::create(
            [
                'name' => $request->name,
                'building_id' => $request->building_id
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
    public function show($id)
    {
        //
        $area = Area::where('id', $id)->first();
        $buildings = Building::all();
        return view('admin.area.details', compact('area', 'buildings'));
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
        //
        $this->validate($request, 
            [
                'name' => 'required',
                'building_id' => 'required',
            ]
        );
        $result = Area::where('id', $id)->first();
        if (!$result) {
            return back()->with('error', 'Not found');
        }
        $result->update(
            [
                'name' => $request->name,
                'building_id' => $request->building_id,
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
        $result = Area::where('id', $id)->first();
        if (!$result) {
            return back()->with('error', 'Not found');
        }
        $result->delete();

        return redirect()->route('area')->with('success', 'Deleted successfully');
    }
}
