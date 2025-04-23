<?php

namespace App\Http\Controllers;

use App\Models\Announ;
use App\Models\ConditionAnnoun;
use App\Models\Conditions;
use App\Models\InfrastructureAnnoun;
use App\Models\Pictures;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UpdateAnnounController extends Controller
{
    public function getDataInfo(Request $request){
        $announ = Announ::where('id', $request->announ['id'])
        ->withTrashed()
        ->first();
        if ($announ['deal_type'] == 'Аренда'&& $announ['type_real_estate']=='Жилая') {
            $conditions = ConditionAnnoun::select('conditions.*')
            ->join('conditions', 'condition_announs.condition_id', '=', 'conditions.id')
            ->where('condition_announs.announ_id', $request->announ['id'])
            ->get();
            $announ['condition'] = $conditions; 
        }
        if ($announ['type_real_estate']=='Коммерческая') {
            $infrastructure = InfrastructureAnnoun::select('infrastructures.*')
            ->join('infrastructures', 'infrastructure_announs.infrastructure_id', '=', 'infrastructures.id')
            ->where('infrastructure_announs.announ_id', $request->announ['id'])
            ->get();
            $announ['infrastructure'] = $infrastructure; 
        }
        $pictures = Pictures::where('announ_id',$request->announ['id'])->get();
        $imageFiles = glob(public_path('large/*'));
        $images = [];

        foreach ($imageFiles as $imageFile) {
            $imagePath = 'large/' . basename($imageFile);
            $imageContents = $request->file('images');
            $images[] = [
                'name' => basename($imageFile),
                'url' => asset($imagePath),
                'content' => $imageContents,
            ];
        }
        $announ['pictures'] = $pictures; 
        $announ['images'] = $images;
        return $announ;
    }

    public function updateAnnoun(Request $request){
        // $data = json_decode($request->input('all-data'),true);
            // $images = $request->file('images');
            // $filename = [];
            // $names = [];
            // foreach ($images as $image) {
            //     $names[] = $name = uniqid() . '.' . $image->getClientOriginalExtension();
            //     $filename[] = [
            //         'name'=>$name,
            //         'xsmall' => 'xsmall/' . $name,
            //         'small' => 'small/' . $name,
            //         'medium' => 'medium/' . $name,
            //         'large' => 'large/' . $name,
            //     ];
    
            //     $img =  Image::make($image);
            //     $img->fit(2250, 950)->save(public_path('large/' . $name));
    
            //     $img->fit(1400, 800)->save(public_path('medium/' . $name));
    
            //     $img->fit(700, 400)->save(public_path('small/' . $name));
    
            //     $img->fit(350, 200)->save(public_path('xsmall/' . $name));  
            // }

            // for ($i=0; $i < count($dataImage); $i++) { 
            //     Pictures::update([
            //     'pictures'=>$names[$i],
            //     'position'=>$dataImage[$i]['position'],
            //     'rotation'=>$dataImage[$i]['rotation'],
            //     'announ_id'=>$announ->id]);
            // }
        $data = $request;
        // $announ = json_decode($data['announ'],true);
        $allData = [];
        foreach ($data->img as $value) {
            $allData[] = $value['pictures'];
        }

        $pictures = Pictures::where('announ_id',$data->announ['id'])->get();
        $picturesId = [];
        foreach ($pictures as $value) {
            $picturesId[] = $value['pictures'];
        }
        $deletedPicures  = array_diff($picturesId, $allData);
        Pictures::WhereIn('pictures', $deletedPicures)->forceDelete();
        return $deletedPicures;
    }

    public function restoreData(Request $request){
        $announ = Announ::withTrashed()->where('id',$request->announ['id'])->restore();
        return $announ;
    }
}
