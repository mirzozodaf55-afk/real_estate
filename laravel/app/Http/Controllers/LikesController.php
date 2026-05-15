<?php

namespace App\Http\Controllers;

use App\Models\Announ;
use App\Models\Likes;
use App\Models\Owner;
use App\Models\Pictures;
use Illuminate\Http\Request;

class LikesController extends Controller
{
    public function setLikes(Request $request){
        // $like = Likes::where('user_id',$request['user_id'])->where('announ_id',$request['announ_id'])->first();
        // // $likes = [];
        // if ($like) {
        //     $like->delete();
        // }else{
            
        //     Likes::create([
        //         'user_id'=>$request['user_id'],
        //         'announ_id'=>$request['announ_id']
        //     ]);
        // }
        // $allLikes = Likes::all();
        // $announ = Announ::where('id',$request['announ_id'])->first();
        // // $announ->likes = $likes;
        // return [$allLikes];
        
            $user  = Owner::findOrFail($request->user()->id);
            // $user  = Owner::where('id',$request['user_id'])->first();
            $user->LikedAnnouns()->toggle($request['announ_id']);
            $countLikes = Announ::findOrFail($request['announ_id'])->LikedUsers()->count();
            return response()->json([
                'likes-count'=>$countLikes,
                'liked'=>$user->LikedAnnouns()->where('announ_id',$request['announ_id'])->exists(),
                'user'=>$request->user()->id
            ]);
    }

    public function getFavorite(Request $request){
        $announ = Announ::join('likes', 'announs.id', '=', 'likes.announ_id')
            ->where('likes.user_id', $request->user()->id)
            ->select('announs.*')
            ->get();

            
            for ($i=0; $i < count($announ); $i++) { 
                $announ[$i]['images'] = Pictures::where('announ_id',$announ[$i]['id'])->get();
                
            }
            return $announ;
    }
}
