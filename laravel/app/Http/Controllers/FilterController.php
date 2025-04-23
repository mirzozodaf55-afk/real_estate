<?php

namespace App\Http\Controllers;

use App\Models\Announ;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    public function FiltersAnnoun(Request $request)
    {

        $query = Announ::where('type_real_estate','Жилая');
            
            if ($request->city) {
                $city = $request->city;
                $query->where('city', $city);
            }
                
        
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
        
            if ($request->priceFrom) {
                $query->where('price', '>=',$request->priceFrom);
            }
            if ($request->priceTo) {
                $query->where('price', '<=',$request->priceTo);
            }
            
            
            if ($request->totalAreaFrom) {
                $query->where('total_area','>=',$request->totalAreaFrom);  
            }
            
            if ($request->totalAreaTo) {
                $query->where('total_area','<=',$request->totalAreaTo); 
            }

            if ($request->areaFrom) {
                $query->where('living_area','>=',$request->areaFrom);  
            }

            if ($request->areaTo) {
                $query->where('living_area','<=',$request->areaTo);  
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

            if ($request->landAreaFrom) {
                $query->where('land_area','>=',$request->landAreaFrom);  
            }

            if ($request->landAreaTo) {
                $query->where('land_area','<=',$request->landAreaTo);  
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
            $query->where('deleted_at',null);
            return $query->get();
    }
}

