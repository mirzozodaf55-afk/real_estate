<?php

namespace App\Http\Controllers;

use App\Models\Announ;
use App\Models\ConditionAnnoun;
use App\Models\Conditions;
use App\Models\InfrastructureAnnoun;
use App\Models\Pictures;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class deleteAnnounController extends Controller
{
    public function deleteAnnoun(Request $request){
        $images = Pictures::where('announ_id',$request->announ['id'])->get();
        $large = public_path('large');
        $medium = public_path('medium');
        $small = public_path('small');
        $xsmall = public_path('xsmall');

        for ($i=0; $i < count($images); $i++) {
            $largePath = $large .'/'.$images[$i]->pictures;
            $mediumPath = $medium .'/'.$images[$i]->pictures;
            $smallPath = $small .'/'.$images[$i]->pictures;
            $xsmallPath = $xsmall .'/'.$images[$i]->pictures;
            // if (File::exists($largePath)) {
            //     File::delete($largePath);
            // }
            // if (File::exists($mediumPath)) {
            //     File::delete($mediumPath);
            // }
            // if (File::exists($smallPath)) {
            //     File::delete($smallPath);
            // }
            // if (File::exists($xsmallPath)) {
            //     File::delete($xsmallPath);
            // }
            
        }
        
        Pictures::where('announ_id',$request->announ['id']);
        if ($request->announ['type_real_estate'] === 'Жилая') {
            ConditionAnnoun::where('announ_id',$request->announ['id']);
        }
        if ($request->announ['type_real_estate'] === 'Коммерческая') {
            InfrastructureAnnoun::where('announ_id',$request->announ['id']);
        }
        Announ::where('id',$request->announ['id'])->delete();
        return ['your data successfuly deleted'];
    
        
    }
}
