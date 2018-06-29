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
            $table->string('route')->nullable()->default(null);
            $table->string('street_number')->nullable()->default(null);
            $table->string('administrative_area_level_3')->nullable()->default(null);
            $table->string('administrative_area_level_2')->nullable()->default(null);
            $table->string('administrative_area_level_1')->nullable()->default(null);              
            $table->timestamps();           
        });        
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
