<?php

namespace App\Http\Controllers;
use App\Parking;
use App\User;
use App\Http\Controllers\Controller;
use DB;
use Request;

class ParkingController extends Controller
{  
    
    protected $hidden = ['created_at', 'updated_at'];

    public function postNearby()        
    {               
            
        $input = Request::all();

        $lat_index = floor($input['lat']/0.0017966);
        $lng_index = floor($input['lng']/0.0017966);
        $input = [    
            'lat_index' => $lat_index,
            'lng_index' => $lng_index,        
            'lat' => $input['lat'],
            'lng' => $input['lng'],
            'street_number' => $input['street_number'],
            'route' => $input['route'],
            'administrative_area_level_3' => $input['administrative_area_level_3'],
            'administrative_area_level_2' => $input['administrative_area_level_2'],
            'administrative_area_level_1' => $input['administrative_area_level_1'] 
        ];

        $finalResponse = [];
        $responseByName = Parking::findByName($input); 
        if($responseByName['status'] == 'OK') {
            $finalResponse['parkings'] = $responseByName['parkings'];
            $finalResponse['best_choice_by_name'] = $responseByName['bestChoice'];
            $finalResponse['status'] = 'OK';
        } else {
            $finalResponse['parkings'] = [];
            $finalResponse['best_choice_by_name'] = NULL;
            $finalResponse['status'] == 'EMPTY_RESULTS';    
        }

        $responseByPosition = Parking::findByPosition($input);
        if($responseByPosition['status'] == 'OK') {
            foreach ($responseByPosition['parkings'] as $parking) {
                array_push($finalResponse['parkings'], $parking);
            };
            $finalResponse['best_choice_by_position'] = $responseByPosition['bestChoice'];
            $finalResponse['status'] == 'OK';
        } else {
            $finalResponse['parkings'] = [];
            $finalResponse['best_choice_by_position'] = NULL;              
        }

        return $finalResponse;

        /*$parkings = DB::table('parking')
            ->where('lat_index', $lat_index)
            ->where('lng_index', $lng_index)
            ->select(
                'alias',
                'lat',
                'lng'
            )->get();          

        if(sizeof($parkings) > 0) {
            $best = null;
            
            foreach ($parkings as $parking) {              
                $degtorad = 0.01745329;
                $radtodeg = 57.29577951;
                $dlong = ($lng - $parking->lng);
                $dvalue = (sin($lat * $degtorad) * sin($parking->lat * $degtorad))
                + (cos($lat * $degtorad) * cos($parking->lat * $degtorad)
                * cos($dlong * $degtorad));
                $dd = acos($dvalue) * $radtodeg;
                $miles = ($dd * 69.16);
                $metros = ($dd * 111.302) * 1000;
                $parking->distance = ['u' => 'mts', 'value' => round($metros, 0), 'value_detail' => round($metros, 2)];

                $parking->coords = ['latitude' => (double) $parking->lat, 'longitude' => (double) $parking->lng];

                if($best != null) {
                    if($metros < $best->distance['value_detail']) {
                        $best = $parking;                        
                    }
                } else {
                    $best = $parking;
                }            
            }
        } else {
            $best = null;
        }

        return [
            'status' => 'OK',
            'input' => [
                'lat' => $lat,
                'lng' => $lng,
                'lat_i' => $lat_index,
                'lng_i' => $lng_index
            ],
            'closest' => $best,
            'parkings' => $parkings            
        ];  */      
    }

    public function getNew($lat = null, $lng = null, $alias = null) {
        $input = [
            'lat' => $lat,
            'lng' => $lng
        ];
        return Parking::add($input);
    }
}