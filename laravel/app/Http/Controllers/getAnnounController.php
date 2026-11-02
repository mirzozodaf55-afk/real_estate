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
        // Обложку берём из того же join, а не отдельным списком фото по порядковому номеру:
        // раньше i-е фото доставалось i-му объявлению (чужие обложки), а при разной длине списков был 500.
        // Снятые с публикации (withTrashed) тоже больше не отдаём.
        $allData = Announ::join('pictures', 'announs.id', 'pictures.announ_id')
            ->where('pictures.position', 0)
            ->whereNull('pictures.deleted_at')
            ->select('announs.*', 'pictures.pictures as image')
            ->get();
        $visitor_user = VisitorUser::where('ip-adress',$request->ip())->first();
        return [$allData,$visitor_user];
    }
}
