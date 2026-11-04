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

    public function getInfrastrucures(){
        $infrastructures = Infrastructure::all();
        return $infrastructures;
    }
}