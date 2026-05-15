<?php

namespace App\Http\Controllers;

use App\Events\MyEvent;
use App\Events\userTyping;
// use App\Events\sendFile;
use App\Models\File;
use App\Models\Message;
use App\Models\messageUser;
use App\Models\Owner;
use App\Models\VisitorUser;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Store;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;

class MessageController extends Controller
{
    public function Message(Request $request){
        $data = $request->validate([
            'text'=>'required',
            'userId'=>'required|integer',
            'friendId'=>'required|integer',
            'type'=>'required',
            'time'=>'required'

        ]);

        $message = Message::create([
            'message'=>$data['text'],
            'user_id'=>$data['userId'],
            'type'=>$data['type'],
            'time'=>$data['time']
        ]);
        messageUser::create([
            'message_id'=>$message['id'],
            'owner_id'=>$data['userId'],
            'friend_id'=>$data['friendId']
        ]);
        $owner = Owner::where('id',$data['userId'])->first();
        $d = event(new MyEvent([
            'type'=>'message',
            'id'=>$message['id'],
            'text'=>$message['message'],
            'user_id'=>$message['user_id'],
            'time'=>$message['time'],
            'avatar'=>auth()->user()->avatar
        ]));
        return $message;
    }

    public function SendFile(Request $request){
        $user_id = (int) $request->input('userId');
        $friend_id = (int) $request->input('friendId');
        $file = $request->file('file');
        $type = $request->input('type');
        $name = uniqid() .'.'. $file->getClientOriginalExtension();
        if ($request->hasFile('file') && $request->input('type')=='picture') {
            $img = Image::make($file->getPathname());
            $img->fit(1024,720)->save(public_path('filemessage/'.$name));
            
        }
        $dbfile = Message::create([
            'message'=>$name,
            'user_id'=>$user_id,
            'type'=>$type,
            'time'=>$request->input('time')
        ]);

        messageUser::create([
            'message_id'=>$dbfile['id'],
            'owner_id'=>$user_id,
            'friend_id'=>$friend_id
        ]);
        event(new \App\Events\sendFile([
            'type'=>$type,
            'id'=>$dbfile['id'],
            'text'=>$dbfile['message'],
            'user_id'=>$user_id,
            'time'=>$request->input('time'),
            'avatar'=>auth()->user()->avatar
        ]));
        return ['success'=>$file];
    }
// public function SendVideo(Request $request){

//     $user_id = (int) $request->input('userId');
//     $friend_id = (int) $request->input('friendId');
//     $file = $request->file('video');
//     $type = $request->input('types');
//     $time = $request->input('time');
    
//     $originalName = uniqid() . '.' . $file->getClientOriginalExtension();
//     $originalPath = public_path("filemessage/original");
//     $resolutions = [
//         '720' => '1280x720',
//         '480' => '854x480',
//         '360' => '640x360',
//         '144' => '240x144',
//     ];

//     // Создаём папку original
//     if (!file_exists($originalPath)) {
//         mkdir($originalPath, 0775, true);
//     }
//     $dbfile = Message::create([
//         'message' => $originalName,
//         'user_id' => $user_id,
//         'type' => $type,
//         'time' => $time,
//     ]);

//     messageUser::create([
//         'message_id' => $dbfile['id'],
//         'owner_id' => $user_id,
//         'friend_id' => $friend_id
//     ]);

//     event(new \App\Events\sendFile([
//         'type' => $type,
//         'id' => $dbfile['id'],
//         'text' => $dbfile['message'],
//         'user_id' => $user_id,
//         'time' => $time,
//         'avatar' => auth()->user()->avatar ?? null,
//     ]));
//     // Сохраняем оригинал
//     $file->move($originalPath, $originalName);
//     $fullOriginalPath = $originalPath . '/' . $originalName;

//     // Создаём видео в нужных разрешениях
//     foreach ($resolutions as $key => $size) {
//         $outputDir = public_path("filemessage/{$key}");
//         if (!file_exists($outputDir)) {
//             mkdir($outputDir, 0775, true);
//         }

//         $outputPath = "{$outputDir}/{$originalName}";
//         $command = "ffmpeg -i \"$fullOriginalPath\" -s $size  -c:a copy \"$outputPath\"";
//         exec($command);
//     }  

//     return ['success' => true];
// } 

public function SendVideo(Request $request){
    $user_id = (int) $request->input('userId');
    $friend_id = (int) $request->input('friendId');
    $file = $request->file('video');
    $type = $request->input('types');
    $time = $request->input('time');

    $originalName = uniqid() . '.' . $file->getClientOriginalExtension();
    $originalPath = public_path("filemessage/original");
    $file->move($originalPath, $originalName);
    $dbfile = Message::create([
        'message' => $originalName,
        'user_id' => $user_id,
        'type' => $type,
        'time' => $time,
    ]);

    messageUser::create([
        'message_id' => $dbfile['id'],
        'owner_id' => $user_id,
        'friend_id' => $friend_id
    ]);

    event(new \App\Events\sendFile([
        'type' => $type,
        'id' => $dbfile['id'],
        'text' => $dbfile['message'],
        'user_id' => $user_id,
        'time' => $time,
        'avatar' => auth()->user()->avatar ?? null,
    ]));




    // Сохраняем в базу данных
    

    return response()->json([
        'success' => true,
        'video_url' => asset("filemessage/{$originalName}")
    ]);
}

    
    public function getMessage(Request $request){
        $request->user()->update(['last_seen_at' => now()]);
        $owner = Owner::where('uniq_id', $request->owner)->first();
        $friend = Owner::where('uniq_id', $request->friend)->first();
        $user = Owner::all();
        $mess = Message::all();
    
        // Получаем сообщения в обе стороны
        $user_message = messageUser::where(function ($query) use ($owner,$friend) {
                $query->where('owner_id', $owner['id'])
                      ->where('friend_id', $friend['id']);
            })
            ->orWhere(function ($query) use ($owner,$friend) {
                $query->where('owner_id', $friend['id'])
                      ->where('friend_id', $owner['id']);
            })
            ->get();
    
        $message = [];
    
        foreach ($user_message as $value) {
            $message[] = Message::where('id', $value->message_id)->first();
        }
    
        // Привязываем аватар к сообщениям
        foreach ($message as $msg) {
            $msg['avatar'] = Owner::where('id', $msg['user_id'])->first(['avatar']);
        }
    
        return response()->json([
            'message' => $message,
            'users' => ['owner' => $owner, 'friend' => $friend],
            'user_mes' => $user_message,
            'user'=>$user
        ]);
    }
    

    public function Typing(Request $request){
        $request->user()->update(['last_seen_at' => now()]);
        event(new userTyping([
           'value'=>$request->value,
           'userId'=> $request->userId,
           'userName'=>$request->userName,
           'lastName'=>$request->lastName,
           'typing'=>$request->typing
        ]));
    }

    public function getAllUser(Request $request){
        // $request->user()->update(['last_seen_at' => now()]);
        $alluser = Owner::all()->map(function($user){
            $lastSeen = $user->last_seen_at ? Carbon::parse($user->last_seen_at):null;
            $isOnline = false;
            if ($lastSeen) {
                $isOnline = $lastSeen->greaterThan(now()->subSeconds(5));
            }
            return [
                'id'=>$user->id,
                'name'=>$user->name,
                'last_name'=>$user->last_name,
                'avatar'=>$user->avatar,
                'phone_number'=>$user->phone_number,
                'online'=>$isOnline,
                'last_seen_at'=>$lastSeen?$lastSeen->diffForHumans():null,
                'uniq_id'=>$user->uniq_id

            ];
        });
        $visitorUser = VisitorUser::all();
        return [$alluser,$visitorUser];
    }


    public function getVideo(Request $request){
        $request->user()->update(['last_seen_at' => now()]);
        $video = Message::where('type','video')->get();

        return response()->json($video);

    }
}
