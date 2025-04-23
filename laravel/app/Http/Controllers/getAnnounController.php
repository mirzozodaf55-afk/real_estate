<?php

namespace App\Http\Controllers;

use App\Models\Announ;
use App\Models\Pictures;
use App\Models\VisitorUser;
use Illuminate\Http\Request;

class getAnnounController extends Controller
{
    // public function getData(){
    //     $announ = Announ::all();
    //     return $announ;
    // }

    public function getData(Request $request){
        $pictures = Pictures::where('position','<',1)->get();
        
        $announ = Announ::join('pictures','announs.id', 'pictures.announ_id')
        ->withTrashed()
        ->where('pictures.position', 0)
        ->select('announs.*')
        ->get();
        $allData = [];
        for ($i=0; $i < count($pictures); $i++) { 
            $announ[$i]['image'] = $pictures[$i]['pictures'] ??'deafault.jpg';
            // if ($picture[$i] == null) {
            //     $picture[$i] = [
            //         'pictures'=>'deafault.jpg'
            //     ];
            // }
            $allData[] =  $announ[$i];
        }
        $visitor_user = VisitorUser::where('ip-adress',$request->ip())->first();
        return [$allData,$visitor_user];
    }
}
