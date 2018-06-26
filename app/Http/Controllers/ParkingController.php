<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;
use DB;

class ParkingController extends Controller
{  
    public function getNearby($lat = null, $lng = null)
    {        
        $lat_index = floor($lat/0.0017966);
        $lng_index = floor($lng/0.0017966);

        $parkings = DB::table('parking')
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
        ];        
    }

    public function getNew($lat = null, $lng = null, $alias = null) {
        try {
            $lat_index = floor($lat/0.0017966);
            $lng_index = floor($lng/0.0017966);
            
            DB::table('parking')->insert([
                'alias' => $alias,
                'lat' => $lat,
                'lng' => $lng,
                'lat_index' => $lat_index,
                'lng_index' => $lng_index
            ]);
            return ['status' => 'OK', 'message' => 'New parking has been added.'];
        } catch (Exception $e) {
            return ['status' => 'FAIL', 'err'=> $e->getMessage()];
        }
    }
}