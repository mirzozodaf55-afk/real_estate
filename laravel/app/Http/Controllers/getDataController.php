<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Conditions;
use App\Models\Infrastructure;
use Illuminate\Http\Request;

class getDataController extends Controller
{
    public function getCities(){
        $city = City::all();

        return $city;
    }

    public function getCondition(){
        $condition = Conditions::all();
        return $condition;
    }

    public function createInfrastrucures(Request $request){
        
         $data = $request->json()->all();

            foreach ($data as $value) {
                 Infrastructure::firstOrCreate(['name'=>$value]);
                
            }
         
            
    }

    public function createCity(Request $request){

        $cities = $request->json()->all();
        $saveCities = [];
        foreach ($cities as $city) {
            $c = City::firstOrCreate(['name' => $city]);
            $saveCities[] = $c;
        }
        return response()->json($saveCities);
    }

    public function createFurniture(Request $request){
        $furnitures = $request->json()->all();
        $d = [];
        foreach ($furnitures as $furniture) {
            $d[] = Conditions::firstOrCreate(['name'=>$furniture]);
        }
        return response()->json($d);
    }

    

    public function getInfrastrucures(){
        $infrastructures = Infrastructure::all();
        return $infrastructures;
    }
}