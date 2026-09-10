<?php

namespace App\Http\Controllers;

use App\Models\Announ;
use App\Models\Owner;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CreateOwnerController extends Controller
{
    public function createOwner(Request $request){
        $phone = $request->phone;
        $today = Carbon::now();
        $today->format('Y-m-d H:i:s');

        $verificationCode = strval(random_int(100000, 999999));
        $uniq_id = strval(random_int(00000000,99999999));
        $owner = Owner::firstOrCreate([
            'phone_number' => $phone
        ]);
        $owner->where('phone_number',$phone)
        ->update(['verification_code' => $verificationCode,'uniq_id'=>$uniq_id]);
        $token = $owner->createToken('user');
        return [$verificationCode,$token->plainTextToken];
    }
    public function verifyUser(Request $request){
        $user = Owner::where('verification_code',$request->code)->get();
        return $user;
    }
    
    public function UpdateOwner(Request $request){
        $request->user()->update(['last_seen_at' => now()]);
        $owner = Owner::where('phone_number', $request->phone)->first();
       
             $owner->update([
                'name'=>$request->name,
                'last_name'=>$request->lastName,
                'birth_date'=>$request->date
                ]
             );
            
            return $owner;
    }

    public function getUser(Request $request){
        $user = Owner::where('phone_number',$request->user()->phone_number)->first();
        // $request->user()->update(['last_seen_at'=>now()]);
        
        // if ($request->user()->last_seen_at === null || now()->diffInSeconds($request->user()->last_seen_at) > 60) {
            $request->user()->update(['last_seen_at' => now()]);
        // }
        return $user;
    }


    public function SendMessage(Request $request){
        $owner = Owner::where('phone_number', $request->phone)->first();
        $owner->update(['email'=>$request->email]);
        return $owner;
    }

    public function getOwnerFromAnnoun(Request $request){
        $owner = Owner::WhereIn('id', $request->owner_id)->get();
        $announ  = Announ::WhereIn('owner_id',$request->owner_id)->get();
        
        return [$announ, $owner];
    } 
    
    
}
