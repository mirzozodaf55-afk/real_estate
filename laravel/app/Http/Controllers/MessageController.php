<?php

namespace App\Http\Controllers;

use App\Events\MyEvent;
use App\Events\userTyping;
// use App\Events\sendFile;
use App\Models\File;
use App\Models\Message;
use App\Models\messageUser;
use App\Models\Owner;
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
//     public function SendVideo(Request $request){

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

public function SendVideo(Request $request)
{
    $user_id = (int) $request->input('userId');
    $friend_id = (int) $request->input('friendId');
    $file = $request->file('video');
    $type = $request->input('types');
    $time = $request->input('time');

    $originalName = uniqid() . '.' . $file->getClientOriginalExtension();
    $originalPath = public_path("filemessage/original");
    // $hlsPath = public_path("filemessage/{$originalName}");
    $file->move($originalPath, $originalName);
    // Создание директорий
    // if (!file_exists($originalPath)) mkdir($originalPath, 0775, true);
    // if (!file_exists($hlsPath)) mkdir($hlsPath, 0775, true);
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
    
//     $inputPath = $originalPath . '/' . $originalName;

//     $ffmpegCmd = <<<EOL
// ffmpeg -i "$inputPath" -filter_complex \
// "[0:v]split=4[v1][v2][v3][v4]; \
// [v1]scale=w=1280:h=720[v1out]; \
// [v2]scale=w=854:h=480[v2out]; \
// [v3]scale=w=640:h=360[v3out]; \
// [v4]scale=w=256:h=144[v4out]" \
// -map "[v1out]" -c:v:0 h264 -b:v:0 3000k -map a:0 -c:a:0 aac -f hls -hls_time 4 -hls_playlist_type vod -hls_segment_filename "{$hlsPath}/720p_%03d.ts" {$hlsPath}/720p.m3u8 \
// -map "[v2out]" -c:v:1 h264 -b:v:1 1600k -map a:0 -c:a:1 aac -f hls -hls_time 4 -hls_playlist_type vod -hls_segment_filename "{$hlsPath}/480p_%03d.ts" {$hlsPath}/480p.m3u8 \
// -map "[v3out]" -c:v:2 h264 -b:v:2 800k  -map a:0 -c:a:2 aac -f hls -hls_time 4 -hls_playlist_type vod -hls_segment_filename "{$hlsPath}/360p_%03d.ts" {$hlsPath}/360p.m3u8 \
// -map "[v4out]" -c:v:3 h264 -b:v:3 400k  -map a:0 -c:a:3 aac -f hls -hls_time 4 -hls_playlist_type vod -hls_segment_filename "{$hlsPath}/144p_%03d.ts" {$hlsPath}/144p.m3u8
// EOL;

//     exec($ffmpegCmd);

    // Генерация master.m3u8
//     $masterM3U8 = <<<M3U8
// #EXTM3U
// #EXT-X-VERSION:3
// #EXT-X-STREAM-INF:BANDWIDTH=3000000,RESOLUTION=1280x720
// 720p.m3u8
// #EXT-X-STREAM-INF:BANDWIDTH=1600000,RESOLUTION=854x480
// 480p.m3u8
// #EXT-X-STREAM-INF:BANDWIDTH=800000,RESOLUTION=640x360
// 360p.m3u8
// #EXT-X-STREAM-INF:BANDWIDTH=400000,RESOLUTION=256x144
// 144p.m3u8
// M3U8;

//     file_put_contents("{$hlsPath}/master.m3u8", $masterM3U8);

    // Сохраняем в базу данных
    

    return response()->json([
        'success' => true,
        'video_url' => asset("filemessage/hls/{$originalName}_hls/master.m3u8")
    ]);
}

    
    public function getMessage(Request $request){
        $owner = Owner::where('id', $request->owner)->first();
        $friend = Owner::where('id', $request->friend)->first();
        $user = Owner::all();
        $mess = Message::all();
    
        // Получаем сообщения в обе стороны
        $user_message = messageUser::where(function ($query) use ($request) {
                $query->where('owner_id', $request->owner)
                      ->where('friend_id', $request->friend);
            })
            ->orWhere(function ($query) use ($request) {
                $query->where('owner_id', $request->friend)
                      ->where('friend_id', $request->owner);
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
        event(new userTyping([
           'value'=>$request->value,
           'userId'=> $request->userId,
           'userName'=>$request->userName,
           'typing'=>$request->typing
        ]));
    }

    public function getAllUser(Request $request){
        $alluser = Owner::all();
        return $alluser;
    }


    public function getVideo(Request $request){

        $video = Message::where('type','video')->get();

        return response()->json($video);

    }
}
