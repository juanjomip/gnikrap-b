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
        // Configura el Input.
        $input = Request::all();
        $lat_index = floor($input['lat']/0.0017966);
        $lng_index = floor($input['lng']/0.0017966);

        $input = Parking::reverseGeoLocation($input);

        $input['lat_index'] = $lat_index;
        $input['lng_index'] = $lng_index;        

        

        // Contiene la respuesta final
        $finalResponse = [];

        // Obtiene los estacionamientos y la mejor opción por nombre.
        $responseByName = Parking::findByName($input); 
        if($responseByName['status'] == 'OK') {
            $finalResponse['parkings'] = $responseByName['parkings'];
            $finalResponse['best_choice_by_name'] = $responseByName['bestChoice'];
            $finalResponse['status'] = 'OK';
        } else {
            $finalResponse['parkings'] = [];
            $finalResponse['best_choice_by_name'] = NULL;
            $finalResponse['status'] = 'EMPTY_RESULTS';    
        }

        // Obtiene los estacionamientos y la mejor opción por posición.
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

        foreach ($finalResponse['parkings'] as &$parking) {
            $parking->price = round(rand(20,40), 0);
        }

        $finalResponse['input'] = $input;
        return $finalResponse;       
    }

    // Permite registrar un nuevo estacionamiento por posición.
    public function getNew($lat = null, $lng = null, $alias = null) {
        $input = [
            'lat' => $lat,
            'lng' => $lng
        ];
        return Parking::add($input);
    }

    public function getNewbyalias($alias) {
        $input = [
            'alias' => $alias            
        ];
        return Parking::addByAlias($input);
    }
}