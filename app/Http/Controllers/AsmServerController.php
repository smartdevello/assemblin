<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AsmServerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    public function getSERVERConfig()
    {
        try{
            $filepath = config()->get('constants.BASE_CONFIG_PATH') . 'asmserver/config.json';
            $myfile = fopen($filepath, "rw") or die("Unable to open file!");
            $content = fread($myfile, filesize($filepath));
            fclose($myfile);
            return json_decode($content);
        } catch (\Exception $e){
            return response()->json([
                'error' => $e->getMessage()
            ], 403);
        }

    }
    public function getRESTconfig(Request $request)
    {
        if (!empty($request['name']) ) {
            try{
                $filepath = config()->get('constants.BASE_CONFIG_PATH') . 'asmrest/' . $request['name'] . ".json";
                $myfile = fopen($filepath, "rw") or die("Unable to open file!");
                $content = fread($myfile, filesize($filepath));
                fclose($myfile);
                return json_encode($content);

            }catch (\Exception $e){
                return response()->json([
                    'error' => $e->getMessage()
                ], 403);
            }

        } else {
            return response()->json([
                'error' => 'name parameter is empty!'
            ], 404);
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
