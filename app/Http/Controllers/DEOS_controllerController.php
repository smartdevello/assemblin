<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DEOS_controller;
use App\Models\Area;

class DEOS_controllerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $controllers = DEOS_controller::all();
        $areas = Area::all();
        return view('admin.controller.index', compact('controllers', 'areas'));

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
                'area_id' => 'required'
            ]
        );

        DEOS_controller::create(
            [
                'name' => $request->name,
                'area_id' => $request->area_id
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
        $controller = DEOS_controller::where('id', $id)->first();
        $areas = Area::all();
        return view('admin.controller.details', compact('controller', 'areas'));
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
                'area_id' => 'required',
            ]
        );
        $result = DEOS_controller::where('id', $id)->first();
        if (!$result) {
            return back()->with('error', 'Not found');
        }
        $result->update(
            [
                'name' => $request->name,
                'area_id' => $request->area_id,
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
    }
}
