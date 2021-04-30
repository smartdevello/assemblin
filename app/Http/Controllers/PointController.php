<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PointController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public $api_uri = 'https://172.21.8.245:8000';
    public function getPoints()
    {
        //
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->api_uri. '/assemblin/points/byid');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result);
    }
    public function WritePointsbyid(Request $request){

        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => $this->api_uri. '/assemblin/points/writebyid',
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => '[
                {
                    "id" : "Sigfox4",
                    "value": "5.000000"
                }
            ]',
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Accept: application/json"
            ),
        ));


        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $result;
        return json_decode($result);
    }
    public function getTrendValues(){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->api_uri. '/assemblin/trends/values');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result);
    }
    public function getTrendPoints(){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->api_uri. '/assemblin/trends');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result);
    }
    public function getReadablePoints(){

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->api_uri. '/assemblin/points/readablebyid');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result);

    }
    public function getWritablePoints(){

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->api_uri. '/assemblin/points/writeablebyid');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return json_decode($result);

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
