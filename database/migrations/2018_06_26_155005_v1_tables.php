<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class V1Tables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parking', function (Blueprint $table) {
            $table->increments('id');    
            $table->string('alias'); 
            $table->decimal('lat', 10, 6);
            $table->decimal('lng', 10, 6);       
            $table->string('lat_index');
            $table->string('lng_index');               
            $table->timestamps();           
        });

        $parkings = [
            array('lat' => -33.4009145 ,'lng' => -70.5554036, 'alias' => 'Las tranqueras'),
            array('lat' => -33.4006401 ,'lng' => -70.5553726, 'alias' => 'Pedro gamboa'),
            array('lat' => -33.4019307 ,'lng' => -70.5549962, 'alias' => 'Leonardo Da Vinci'),
            array('lat' => -33.4029646 ,'lng' => -70.5566925, 'alias' => 'Los barbechos'),
            array('lat' => -33.4024964 ,'lng' => -70.5585891, 'alias' => 'Los Arados')            
        ];

        foreach ($parkings as $parking) {
            $lat_index = floor($parking['lat']/0.0017966);
            $lng_index = floor($parking['lng']/0.0017966);
            
            DB::table('parking')->insert([
                'alias' => $parking['alias'],
                'lat' => $parking['lat'],
                'lng' => $parking['lng'],
                'lat_index' => $lat_index,
                'lng_index' => $lng_index
            ]);            
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('parking');
    }
}
