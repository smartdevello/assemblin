<?php

namespace App\Http\Traits;
use SimpleXMLElement;

trait WeatherForcastTrait {
    public function getWeatherData($longitude = 61.0162, $latitude = 25.7647)
    {

          $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://opendata.fmi.fi/wfs?service=WFS&version=2.0.0&request=getFeature&storedquery_id=fmi::forecast::harmonie::surface::point::timevaluepair&latlon=' . $longitude . ',' . $latitude . '&parameters=temperature,windspeedms,PrecipitationAmount',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);

          dd($response);
          $xml = new SimpleXMLElement($response);
          $ns = $xml->getDocNamespaces();
          
          $json = [];
          foreach( $xml->children($ns["wfs"]) as $key=>$member){
              $id = ""; 
          
              foreach ( $member->xpath("descendant::wml2:MeasurementTimeseries") as $MeasurementTimeseries)
              {
                  $id = $MeasurementTimeseries->attributes($ns["gml"]);
                  $id = (string)$id;
                  $json[$id] = [];
                  $points = $MeasurementTimeseries->xpath("descendant::wml2:MeasurementTVP");
                  
                  foreach ($points as $point){
                      $items = $point->children($ns["wml2"]);
                      $json[$id][] = array(
                          'time' => (string)$items[0],
                          'value' => (string)$items[1]
                      );            
                  }
          
              }
          }
        
          return $json;
    }
}