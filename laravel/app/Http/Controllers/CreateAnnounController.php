<?php

namespace App\Http\Controllers;

use App\Events\NotifyMe as EventsNotifyMe;
use App\Models\Announ;
use App\Models\ConditionAnnoun;
use App\Models\Facilities;
use App\Models\FacilitiesAnnoun;
use App\Models\Infrastructure;
use App\Models\InfrastructureAnnoun;
use App\Models\Likes;
use App\Models\Message;
use App\Models\messageUser;
use App\Models\NotifyMe;
use App\Models\Owner;
use Intervention\Image\Facades\Image;
use App\Models\Pictures;
use Illuminate\Http\Request;
class CreateAnnounController extends Controller
{
public function createResidentAnnoun(Request $request){
        $announs = json_decode($request->input('announs'),true);
        $dataImage = json_decode($request->input('dataImage'),true);
        // return $announs;
        $announ = Announ::firstOrCreate([
            'deal_type' => $announs['rent'] ?? null,
            'type_real_estate' => $announs['Estate'] ?? null,
            'type_object' => $announs['objects'] ?? null,
            'rental_type' => $announs['typeRent'] ?? null,
            'coord_x' => $announs['coord_x'] ?? null,
            'coord_y' => $announs['coord_y'] ?? null,
            'floor' => $announs['floor'] ?? null,
            'floor_in_house' => $announs['floorHouse'] ?? null,
            'year_of_construction' => $announs['year'] ?? null,
            'house_type' => $announs['selectType'] ?? null,
            'total_area' => $announs['generalArea'] ?? null,
            'kitchen_area' => $announs['kitchenArea'] ?? null,
            'living_area' => $announs['liveArea'] ?? null,
            'quantity_room' => $announs['quantityRoom'] ?? null,
            'repair' => $announs['repair'] ?? null,
            'elevator' => $announs['elevator'] ?? null,
            'Balcony' => $announs['balcon'] ?? null,
            'parking' => $announs['parking'] ?? null,
            'quantity_bedrooms' => $announs['quantityBedroom'] ?? null,
            'Land_category' => $announs['landCategry'] ?? null,
            'condition_of_the_home' => $announs['conditionHome'] ?? null,
            'electricity' => $announs['electricity'] ?? 'есть',
            'title' => $announs['title'] ?? null,
            'description' => $announs['description'] ?? null,
            'price' => $announs['price'] ?? null,
            'rental_period' => $announs['period'] ?? null,
            'house_area' => $announs['homeArea'] ?? null,
            'land_area' => $announs['landArea'] ?? null,
            'allowKids' => $announs['allowKids'] ?? 'нет',
            'allowAnimal' => $announs['allowAnimals'] ?? 'нет', 
            'building_area'=>$announs['buildingArea']??null,
            'state'=>$announs['State']??null,
            'numberSeats'=>$announs['numberSeats']??null,
            'layout'=>$announs['layout']??null,
            'furniture'=>$announs['Furniture']??null,
            'CeilingHeight'=>$announs['CeilingHeight']??null,
            'owner_id' => $request->user()->id
        ]);

            $images = $request->file('images');
            $filename = [];
            $names = [];
            foreach ($images as $image) {
                $names[] = $name = uniqid() . '.' . $image->getClientOriginalExtension();
                $filename[] = [
                    'name'=>$name,
                    'xsmall' => 'xsmall/' . $name,
                    'small' => 'small/' . $name,
                    'medium' => 'medium/' . $name,
                    'large' => 'large/' . $name,
                ];
    
                $img = Image::make($image);
                $img->fit(2250, 950)->save(public_path('large/' . $name));
    
                $img->fit(1400, 800)->save(public_path('medium/' . $name));
    
                $img->fit(700, 400)->save(public_path('small/' . $name));
    
                $img->fit(350, 200)->save(public_path('xsmall/' . $name));  
            }

            for ($i=0; $i < count($dataImage); $i++) { 
                Pictures::create([
                'pictures'=>$names[$i],
                'position'=>$dataImage[$i]['position'],
                'rotation'=>$dataImage[$i]['rotation'],
                'announ_id'=>$announ->id]);
            }
            
            if (isset($announs['condition']) && is_array($announs['condition'])) {
                foreach ($announs['condition'] as $value) {
                    ConditionAnnoun::firstOrCreate(['condition_id' => $value, 'announ_id' => $announ->id]);
                }
            } 
            if(isset($announs['infrastructure']) && is_array($announs['infrastructure'])){
                foreach ($announs['infrastructure'] as $value) {
                InfrastructureAnnoun::firstOrCreate(['infrastructure_id'=>$value,'announ_id'=>$announ->id]);
                }
            }

            $allfilters = NotifyMe::all();

            function matchesFilters($ad,$filters){
                // if (isset($filters['min_price']) && $ad['price'] < $filters['min_price']) {
                //     return false;
                // }
                // if (isset($filters['max_price']) && $ad['price'] > $filters['max_price']) {
                //     return false;
                // }
            
                // // Проверяем тип сделки
                if (isset($filters['dealType']) && $ad['deal_type'] !== $filters['dealType']) {
                    return false;
                }
            
                // Проверяем тип объекта
                if (isset($filters['typeObject']) && $ad['type_object'] !== $filters['typeObject']) {
                    return false;
                }
            
                // Проверяем локацию (пример — простое сравнение)
                // if (isset($filters['location']) && $ad['location'] !== $filters['location']) {
                //     return false;
                // }
            
                // Если все проверки пройдены — объявление совпадает с фильтром
                return true;
            }

            foreach ($allfilters as $notify) {
                $filters = json_decode($notify->filters, true);
                if(matchesFilters($announ, $filters)){
                    event(new EventsNotifyMe($announ->owner_id,$announ));
                }
            }

            return ['you have successfully placed ads',$allfilters];
        }




    //         if(array_values($commercial[2]) === $commercial[2]){
    //                 foreach ($commercial[2] as $value) {
    //                 InfrastructureAnnoun::firstOrCreate(['infrastructure_id'=>$value,'announ_id'=>$announ->id]);
    //                 }
    //             }else{
    //                 return;
    //             }
    //             return [$announ->id];
    //         }
            
    

    public function getAnnouns(Request $request){

        $query = Announ::where('type_real_estate','Жилая');
            
    
        if ($request->dealType) {
            $dealType = $request->dealType;
            $query->where('deal_type', $dealType);
        }
    
        if ($request->buildingType) {
            $buildingType = $request->buildingType;
            $query->WhereIn('type_object', $buildingType);
        }else if ($request->typeObject) {
            $query->where('type_object',$request->typeObject);
        }

        if ($request->quantityRoom) {
            $quantityRoom = $request->quantityRoom;
            $query->WhereIn('quantity_room', $quantityRoom);
        }
    
        if ($request->min_price) {
            $query->where('price', '>=',$request->min_price);
        }
        if ($request->max_price) {
            $query->where('price', '<=',$request->max_price);
        }
        
        
    
        if ($request->totalAreaFrom) {
            $query->where('total_area','>=',$request->totalAreaFrom);  
        }
        
        if ($request->totalAreaTo) {
            $query->where('total_area','<=',$request->totalAreaTo); 
        }

        if ($request->areaFrom) {
            $query->where('house_area','>=',$request->areaFrom);  
        }

        if ($request->areaTo) {
            $query->where('house_area','<=',$request->areaTo);  
        }
        if ($request->kitchenAreaFrom) {
            $query->where('kitchen_area','>=',$request->kitchenAreaFrom);  
        }

        if ($request->kitchenAreaTo) {
            $query->where('kitchen_area','<=',$request->kitchenAreaTo);  
        }

        if ($request->floorFrom) {
            $query->where('floor','>=',$request->floorFrom);  
        }

        if ($request->floorTo) {
            $query->where('floor','<=',$request->floorTo);  
        }

        if ($request->floorHouseFrom) {
            $query->where('floor_in_house','>=',$request->floorHouseFrom);  
        }

        if ($request->floorHouseTo) {
            $query->where('floor_in_house','<=',$request->floorHouseTo);  
        }

        if ($request->yearFrom) {
            $query->where('year_of_construction','>=',$request->yearFrom);  
        }
        if ($request->yearTo) {
            $query->where('year_of_construction','<=',$request->yearTo);  
        }

        if ($request->typeHome) {
            $query->WhereIn('house_type', $request->typeHome);
        }

        if ($request->balcon) {
            $query->where('Balcony', $request->balcon);
        }
        if ($request->condition) {
            $query->join('condition_announs','announs.id','=','condition_announs.announ_id')
            ->WhereIn('condition_announs.condition_id',$request->condition)
            ->select('announs.*');
        }
        if ($request->repair) {
            $query->WhereIn('repair',$request->repair);
        }
        if ($request->parking) {
            $query->WhereIn('parking', $request->parking);
        }

        // $query->where('deleted_at',null);

        $data = $query->get();
        $count = [];
        $picture = [];
        $likes = [];
        $user = [
            'ip-adress'=>$request->ip(),
            'user-agent'=>$request->header('user-Agent'),
            'path' => $request->path(),
            'referer'=>$request->header('referer'),
            'fullUrl' => $request->fullUrl()
        ];
        for ($i = 0; $i < count($data) ; $i++) { 
           $picture[] = Pictures::where('announ_id',$data[$i]['id'])->get();
           //->pluck('pictures')->toArray();   
           $count[] = Likes::where('announ_id',$data[$i]['id'])->pluck('id')->toArray();
           $data[$i]['likes-count'] = count($count[$i]); 
           $data[$i]['like'] = $count;
           $data[$i]['isLike'] = false;
           $data[$i]['watch_user'] = $request->user('sanctum')->id;
            for ($j=0; $j < count($count[$i]); $j++) { 
                $likes[] = $count[$i][$j];
            }
        }
        $like = [];
        $ads = [];
        // $like = Likes::where('user_id',$request->user('sanctum')->id)->pluck('announ_id')->toArray();
        // foreach ($like as $key =>  $value) {
        //     $ads[$key][] = Announ::where('id',$value['announ_id'])->get();
        // }


        for ($i=0; $i < count($like); $i++) { 
            $ads[$i] = Announ::where('id',$like[$i]['announ_id'])->first();
            $ads[$i]['isLike'] = true;
        }
        $coord = [];
        foreach ($data as $value) {
            $coord[] = [
                $value['coord_x'],
                $value['coord_y']
            ];
        }

        
        
        return [$data, $picture, $coord, $user, $ads];
    }

  


    public function getAnnounByUser(Request $request){
        $announ = Announ::where('owner_id',$request->user()->id)->withTrashed()
        ->get();
        $data = $announ;
        $picture = [];
        $likes = [];
        for ($i = 0; $i < count($data); $i++) { 
            $picture[$i] = Pictures::where('announ_id',$data[$i]['id'])
            ->get();
            $likes[] = Likes::where('announ_id',$data[$i]['id'])->get();
            $data[$i]['likes'] = count($likes[$i]);
        }
        $allData = [];
        for ($x=0; $x < count($picture); $x++) { 
            if ($picture[$x] == null) {
                $picture[$x] = [
                    'pictures'=>'deafault.jpg'
                ];
            }
            $data[$x]['image'] = $picture[$x];
            $allData[] = $data[$x]; 
            // //$picture[$i]['pictures'];
            // return $picture;
        }
        
        return [$allData,$likes];
      
    }

    public function deleteAcount(Request $request){
            $announ = Announ::where('owner_id',$request['owner_id'])->get();
            // if ($announ) {
                foreach ($announ as $value) {
                    ConditionAnnoun::where('announ_id',$value['owner_id'])->delete();
                    InfrastructureAnnoun::where('announ_id',$value['owner_id'])->delete();
                    Pictures::where('announ_id',$value['owner_id'])->delete();
                    $value->delete();
                }
            // }
            Message::where('user_id',$request['owner_id'])->delete();
            messageUser::where('owner_id',$request['owner_id'])->delete();
            messageUser::where('friend_id',$request['owner_id'])->delete();
            Owner::where('id',$request['owner_id'])->delete();
        return ['delete'=>'account successfully deleted!'];
    }
}
    
