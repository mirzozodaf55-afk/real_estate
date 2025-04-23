<?php

namespace App\Http\Controllers;

use App\Models\Announ;
use App\Models\Pictures;
use Illuminate\Http\Request;

class getDataFromBack extends Controller
{
    public function getData(){
        $announ = Announ::all();
        return $announ;
    }

    public function getPictures(Request $request){
        $id = $request;
        $picture = Pictures::where('position',0)->get();

        $announ = Announ::join('pictures','announs.id', 'pictures.announ_id')
        ->where('pictures.position',0)
        ->select('announs.*')
        ->get();
        $data = [];
        for ($i=0; $i < count($picture); $i++) { 
            $announ[$i]['image'] = $picture[$i]['pictures'];
        }

        return $announ;
    }
}
