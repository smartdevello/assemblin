<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DEOS_point;
use App\Models\DEOS_controller;
class DEOS_pointController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $points = DEOS_point::all();
        $controllers = DEOS_controller::all();
        return view('admin.point.index', compact('points', 'controllers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        $this->validate($request, [
            'name' => 'required',
            'label' => 'required',
            'controller_id' => 'required'
            ]);
        DEOS_point::create([
            'name' => $request->name,
            'label' => $request->label,
            'controller_id' => $request->controller_id
        ]);
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
        //
        $point = DEOS_point::where('id', $id)->first();        
        $controllers = DEOS_controller::all();
        return view('admin.point.details', compact('point', 'controllers'));
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
        $this->validate($request, [
            'controller_id' => 'required',
            'name' => 'required',
            'label' => 'required'
        ]);

        $result = DEOS_point::where('id', $id)->first();
        if (!$result) {
            return back()->with('error', 'Not found');
        }
        $result->update($request->all());
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
        $result = DEOS_point::where('id', $id)->first();
        if (!$result) {
            return back()->with('error', 'Not found');
        }
        $result->delete();
        return redirect()->route('points')->with('success', 'Deleted successfully');

    }
}
