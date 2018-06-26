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
            $best = $parkings[0];
        } else {
            $best = null;
        }

        return [
            'status' => 'OK',
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
            return ['status' => 'OK'];
        } catch (Exception $e) {
            return ['status' => 'FAIL', 'err'=> $e->getMessage()];
        }
    }
}