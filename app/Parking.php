<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Parking extends Model
{
    protected $table = 'parking';

    public $timestamps = true;

    const is_valid_criteria = 100;

    public static function findByName($input) {
        $parkings = Parking::where('administrative_area_level_3', $input['administrative_area_level_3'])
            ->where('administrative_area_level_2', $input['administrative_area_level_2'])
            ->where('administrative_area_level_1', $input['administrative_area_level_1'])
            ->where('route', $input['route'])
            ->get();    

        if(sizeof($parkings) ==  0) {
            return ['status'=> 'EMPTY'];
        }

        foreach ($parkings as $parking) {
            $distance = self::calculateDistance($parking->lat, $parking->lng, $input['lat'], $input['lng']);
            $parking->distance = $distance;
            $parking->coords = ['latitude' => (double) $parking->lat, 'longitude' => (double) $parking->lng];
        }

        $bestChoice = Self::findBestChoice($input, $parkings);
        return [
            'status' => 'OK',
            'bestChoice' => $bestChoice,
            'parkings' => $parkings
        ];                    
    }

    private static function findBestChoice($input, $parkings) {               
        $parkings = Self::sortByStreetNumber($parkings);
        $bestChoice = $parkings[0];        
        if(Self::isValidChoice($bestChoice, $input)) {
            return $bestChoice;
        } else {
            return null;
        }
    }

    private static function sortByStreetNumber ($toOrderArray) {
        
        $inverse = false;

        $field = 'street_number';

        $position = array();
        $newRow = array();
        foreach ($toOrderArray as $key => $row) {             
            $position[$key]  = $row[$field];
            $newRow[$key] = $row;
        }
        if ($inverse) {
            arsort($position);
        }
        else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {     
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }

    private static function isValidChoice($parking, $input) {
        $criteria = self::is_valid_criteria;
        $degtorad = 0.01745329;
        $radtodeg = 57.29577951;
        $dlong = ($input['lng'] - $parking->lng);
        $dvalue = (sin($input['lat'] * $degtorad) * sin($parking->lat * $degtorad))
        + (cos($input['lat'] * $degtorad) * cos($parking->lat * $degtorad)
        * cos($dlong * $degtorad));
        $dd = acos($dvalue) * $radtodeg;
        $miles = ($dd * 69.16);
        $metros = ($dd * 111.302) * 1000;
        if($metros <= $criteria) {
            return true;
        } else {
            return false;
        }
    }

    public static function add($input) {
    	try {
            $lat_index = floor($input['lat']/0.0017966);
            $lng_index = floor($input['lng']/0.0017966);
            
            $parking = new Parking();

            $client = new \GuzzleHttp\Client();
			$res = $client->get('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$input['lat'].','.$input['lng'].'&key=AIzaSyASmwYThmM1MqKZM2Gbwn8XxfNaUl_PI1k&result_type=street_address');

            $jsonObj =  json_decode($res->getBody());
            foreach ($jsonObj->results[0]->address_components as $component) {
                if($component->types[0] === 'street_number') {
                    $parking->street_number = $component->short_name;
                } elseif($component->types[0] === 'route') {
                    $parking->route = $component->short_name;
                } elseif($component->types[0] === 'administrative_area_level_3') {
                    $parking->administrative_area_level_3 = $component->short_name;
                } elseif($component->types[0] === 'administrative_area_level_2') {
                    $parking->administrative_area_level_2 = $component->short_name;
                } elseif($component->types[0] === 'administrative_area_level_1') { 
                    $parking->administrative_area_level_1 = $component->short_name;
                }
            }

            //return $jsonObj->results[0]->address_components;
			           
            $parking->lat = $input['lat'];
            $parking->lng = $input['lng'];
            $parking->lat_index = $lat_index;
            $parking->lng_index = $lng_index;
            $parking->save();

            return ['status' => 'OK', 'message' => 'New parking has been added.', 'data' => $parking];
        } catch (Exception $e) {
            return ['status' => 'FAIL', 'err'=> $e->getMessage()];
        }
    }  

    public static function addByAlias($input) {
        try {      
            
            $parking = new Parking();
            $client = new \GuzzleHttp\Client();          
            
            $res = $client->get('https://maps.googleapis.com/maps/api/geocode/json?address='.$input['alias'].'&key=AIzaSyASmwYThmM1MqKZM2Gbwn8XxfNaUl_PI1k');

            $jsonObj =  json_decode($res->getBody());


            foreach ($jsonObj->results[0]->address_components as $component) {
                if($component->types[0] === 'street_number') {
                    $parking->street_number = $component->short_name;
                } elseif($component->types[0] === 'route') {
                    $parking->route = $component->short_name;
                } elseif($component->types[0] === 'administrative_area_level_3') {
                    $parking->administrative_area_level_3 = $component->short_name;
                } elseif($component->types[0] === 'administrative_area_level_2') {
                    $parking->administrative_area_level_2 = $component->short_name;
                } elseif($component->types[0] === 'administrative_area_level_1') { 
                    $parking->administrative_area_level_1 = $component->short_name;
                }
            }

            $parking->lat = $jsonObj->results[0]->geometry->location->lat;
            $parking->lng = $jsonObj->results[0]->geometry->location->lng;

            $lat_index = floor($parking->lat/0.0017966);
            $lng_index = floor($parking->lng/0.0017966);                       
            $parking->alias = $input['alias'];
            $parking->lat_index = $lat_index;
            $parking->lng_index = $lng_index;
            $parking->save();

            return ['status' => 'OK', 'message' => 'New parking has been added.', 'data' => $parking];
        } catch (Exception $e) {
            return ['status' => 'FAIL', 'err'=> $e->getMessage()];
        }
    }  

    public static function findByPosition($input) {
        $parkings = Parking::where('lat_index', $input['lat_index'])
            ->where('lng_index', $input['lng_index'])  
            ->where('administrative_area_level_3', '!=', $input['administrative_area_level_3'])
            ->where('administrative_area_level_2', '!=', $input['administrative_area_level_2'])
            ->where('administrative_area_level_1', '!=', $input['administrative_area_level_1'])
            ->where('route', '!=', $input['route'])       
            ->get();               

        if(sizeof($parkings) > 0) {
            $best = null;
            
            foreach ($parkings as $parking) {              
                $distance = self::calculateDistance($parking->lat, $parking->lng, $input['lat'], $input['lng']);
                $parking->distance = $distance;
                $parking->coords = ['latitude' => (double) $parking->lat, 'longitude' => (double) $parking->lng];

                if($best != null) {
                    if($distance < $best->distance['value_detail']) {
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
            'bestChoice' => $best,
            'parkings' => $parkings            
        ];        
    } 

    private static function calculateDistance($lat1, $lng1, $lat2, $lng2) {
        $degtorad = 0.01745329;
        $radtodeg = 57.29577951;
        $dlong = ($lng1 - $lng2);
        $dvalue = (sin($lat1 * $degtorad) * sin($lat2 * $degtorad))
        + (cos($lat1 * $degtorad) * cos($lat2 * $degtorad)
        * cos($dlong * $degtorad));
        $dd = acos($dvalue) * $radtodeg;
        //$miles = ($dd * 69.16);
        $metros = ($dd * 111.302) * 1000;
        //$parking->distance = ['u' => 'mts', 'value' => round($metros, 0), 'value_detail' => round($metros, 2)];
        return $metros;
    } 

    public static function reverseGeoLocation($input) {
        try {
            $lat_index = floor($input['lat']/0.0017966);
            $lng_index = floor($input['lng']/0.0017966);
            
            $parking = [];

            $client = new \GuzzleHttp\Client();
            $res = $client->get('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$input['lat'].','.$input['lng'].'&key=AIzaSyASmwYThmM1MqKZM2Gbwn8XxfNaUl_PI1k&result_type=street_address');

            $jsonObj =  json_decode($res->getBody());
            foreach ($jsonObj->results[0]->address_components as $component) {
                if($component->types[0] === 'street_number') {
                    $parking['street_number'] = $component->short_name;
                } elseif($component->types[0] === 'route') {
                    $parking['route'] = $component->short_name;
                } elseif($component->types[0] === 'administrative_area_level_3') {
                    $parking['administrative_area_level_3'] = $component->short_name;
                } elseif($component->types[0] === 'administrative_area_level_2') {
                    $parking['administrative_area_level_2'] = $component->short_name;
                } elseif($component->types[0] === 'administrative_area_level_1') { 
                    $parking['administrative_area_level_1'] = $component->short_name;
                }
            }

            //return $jsonObj->results[0]->address_components;
                       
            $parking['lat'] = $input['lat'];
            $parking['lng'] = $input['lng'];
            $parking['lat_index'] = $lat_index;
            $parking['lng_index'] = $lng_index;
            
            return $parking;

            
        } catch (Exception $e) {
            return $e->getMeesage().' - '.$e->getLine();
        }
    }  
}
