<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function syncVideo(Request $request){
        $currentTime = [];
        event(new \App\Events\VideoTimeUpdate($currentTime));

    }
}
