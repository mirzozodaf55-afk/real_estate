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
            'text'=>'required|string|max:5000',
            'friendId'=>'required|integer|exists:owners,id',
            // фото и видео — только через send-file / send-video: иначе в «message» можно было подложить имя чужого файла
            'type'=>'required|in:message',
            'time'=>'required|string|max:20'
        ]);
        // отправитель — владелец токена, а не присланный userId (раньше можно было писать от чужого имени)
        $senderId = $request->user()->id;

        $message = Message::create([
            'message'=>$data['text'],
            'user_id'=>$senderId,
            'type'=>$data['type'],
            'time'=>$data['time']
        ]);
        messageUser::create([
            'message_id'=>$message['id'],
            'owner_id'=>$senderId,
            'friend_id'=>$data['friendId']
        ]);
        $this->broadcastSafely(fn () => event(new MyEvent([
            'type'=>'message',
            'id'=>$message['id'],
            'text'=>$message['message'],
            'user_id'=>$senderId,
            'friend_id'=>(int) $data['friendId'],
            'time'=>$message['time'],
            'avatar'=>auth()->user()->avatar
        ])));
        $this->notifyRecipient($request->user(), (int) $data['friendId'], 'message', $message['message'], $message['time']);
        return $message;
    }

    /** websocket — только доставка в реальном времени: если сервер сокетов лежит, сообщение уже сохранено и 500 быть не должно */
    private function broadcastSafely(callable $broadcast): void
    {
        try {
            $broadcast();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('websocket недоступен', ['error' => $e->getMessage()]);
        }
    }

    /** Уведомление получателю: счётчик на иконке сообщений и всплывашка */
    private function notifyRecipient(Owner $sender, int $recipientId, string $type, string $text, ?string $time): void
    {
        $preview = match ($type) {
            'picture' => '📷 Фото',
            'video' => '🎬 Видео',
            default => mb_strimwidth($text, 0, 120, '…'),
        };
        $this->broadcastSafely(fn () => event(new \App\Events\NewChatMessage($recipientId, [
            'from' => [
                'id' => $sender->id,
                'role' => $sender->role,
                'name' => $sender->name,
                'last_name' => $sender->last_name,
                'avatar' => $sender->avatar,
                'uniq_id' => $sender->uniq_id,
                'email' => $sender->email,
                'phone_number' => $sender->phone_number,
            ],
            'type' => $type,
            'preview' => $preview,
            'time' => $time,
            'unread_total' => messageUser::where('friend_id', $recipientId)->whereNull('read_at')->count(),
        ])));
    }

    /** Непрочитанные сообщения: общее число и по собеседникам */
    public function unread(Request $request)
    {
        $me = $request->user()->id;
        $rows = messageUser::where('friend_id', $me)->whereNull('read_at')
            ->selectRaw('owner_id, COUNT(*) as count, MAX(message_id) as last_message_id')
            ->groupBy('owner_id')
            ->get();

        $senders = Owner::whereIn('id', $rows->pluck('owner_id'))->get(['id', 'name', 'last_name', 'avatar', 'uniq_id', 'email', 'phone_number'])->keyBy('id');
        $lastMessages = Message::whereIn('id', $rows->pluck('last_message_id'))->get(['id', 'message', 'type', 'time', 'created_at'])->keyBy('id');

        return response()->json([
            'total' => (int) $rows->sum('count'),
            'by_sender' => $rows->map(function ($row) use ($senders, $lastMessages) {
                $last = $lastMessages[$row->last_message_id] ?? null;
                return [
                    'user' => $senders[$row->owner_id] ?? null,
                    'count' => (int) $row->count,
                    'last_message' => $last ? ($last->type === 'message' ? mb_strimwidth($last->message, 0, 120, '…') : ($last->type === 'picture' ? '📷 Фото' : '🎬 Видео')) : null,
                    'last_at' => $last?->created_at,
                ];
            })->filter(fn ($r) => $r['user'])->sortByDesc('last_at')->values(),
        ]);
    }

    /** Отметить прочитанными все сообщения от собеседника */
    public function markRead(Request $request)
    {
        $data = $request->validate(['friend_id' => 'required|integer']);
        messageUser::where('owner_id', $data['friend_id'])
            ->where('friend_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json(['total' => messageUser::where('friend_id', $request->user()->id)->whereNull('read_at')->count()]);
    }

    public function SendFile(Request $request){
        try {
            $request->validate([
                'friendId' => 'required|integer|exists:owners,id',
                'file' => 'required|file|image|max:20480', // 20 МБ
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Фото слишком большое или недопустимый формат. Максимальный размер: 20 МБ.',
                'errors' => $e->errors(),
            ], 422);
        }

        $user_id = $request->user()->id; // отправитель из токена, а не из запроса
        $friend_id = (int) $request->input('friendId');
        $file = $request->file('file');
        $type = 'picture'; // этот маршрут — только для фото (видео — send-video)
        $name = \App\Support\UploadName::make($file, \App\Support\UploadName::IMAGES, 'jpg');
        Image::make($file->getPathname())->fit(1024,720)->save(public_path('filemessage/'.$name));
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
        $this->broadcastSafely(fn () => event(new \App\Events\sendFile([
            'type'=>$type,
            'id'=>$dbfile['id'],
            'text'=>$dbfile['message'],
            'user_id'=>$user_id,
            'friend_id'=>$friend_id,
            'time'=>$request->input('time'),
            'avatar'=>auth()->user()->avatar
        ])));
        $this->notifyRecipient($request->user(), $friend_id, 'picture', $name, $request->input('time'));
        return ['success'=>true];
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
    try {
        $request->validate([
            'friendId' => 'required|integer|exists:owners,id',
            'video' => 'required|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/webm,video/3gpp,video/x-matroska|max:307200', // 300 МБ
        ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => 'Видео слишком большое или недопустимый формат. Максимальный размер: 300 МБ.',
            'errors' => $e->errors(),
        ], 422);
    }

    $user_id = $request->user()->id; // отправитель из токена, а не из запроса
    $friend_id = (int) $request->input('friendId');
    $file = $request->file('video');
    $type = 'video';
    $time = $request->input('time');

    // расширение по содержимому, не из имени файла: иначе «x.php» с видео внутри лёг бы в public/ исполняемым файлом
    $originalName = \App\Support\UploadName::make($file, \App\Support\UploadName::VIDEOS, 'mp4');
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

    $this->broadcastSafely(fn () => event(new \App\Events\sendFile([
        'type' => $type,
        'id' => $dbfile['id'],
        'text' => $dbfile['message'],
        'user_id' => $user_id,
        'friend_id' => $friend_id,
        'time' => $time,
        'avatar' => auth()->user()->avatar ?? null,
    ])));
    $this->notifyRecipient($request->user(), $friend_id, 'video', $originalName, $time);




    // Сохраняем в базу данных
    

    return response()->json([
        'success' => true,
        'video_url' => asset("filemessage/{$originalName}")
    ]);
}

    
    public function getMessage(Request $request){
        $request->user()->update(['last_seen_at' => now()]);
        // role — чтобы в чате с командой сайта показать метку «Администрация»
        $publicFields = ['id', 'name', 'last_name', 'avatar', 'phone_number', 'uniq_id', 'last_seen_at', 'role'];
        $owner = Owner::where('uniq_id', $request->owner)->first($publicFields);
        $friend = Owner::where('uniq_id', $request->friend)->first($publicFields);

        if (!$owner || !$friend) {
            return response()->json(['message' => 'Чат не найден'], 404);
        }

        // Читать переписку может только один из её участников
        $me = $request->user()->id;
        if ($me !== $owner->id && $me !== $friend->id) {
            return response()->json(['message' => 'Нет доступа к этому чату'], 403);
        }

        // Открыл переписку — сообщения собеседника прочитаны
        $other = $me === $owner->id ? $friend->id : $owner->id;
        messageUser::where('owner_id', $other)->where('friend_id', $me)->whereNull('read_at')->update(['read_at' => now()]);

        // Получаем сообщения в обе стороны
        $messageIds = messageUser::where(function ($query) use ($owner,$friend) {
                $query->where('owner_id', $owner->id)
                      ->where('friend_id', $friend->id);
            })
            ->orWhere(function ($query) use ($owner,$friend) {
                $query->where('owner_id', $friend->id)
                      ->where('friend_id', $owner->id);
            })
            ->pluck('message_id');

        $message = Message::whereIn('id', $messageIds)->orderBy('id')->get();

        // Привязываем аватар к сообщениям (в переписке всего два автора)
        $avatars = [
            $owner->id => ['avatar' => $owner->avatar],
            $friend->id => ['avatar' => $friend->avatar],
        ];
        foreach ($message as $msg) {
            $msg['avatar'] = $avatars[$msg->user_id] ?? null;
        }

        return response()->json([
            'message' => $message,
            'users' => ['owner' => $owner, 'friend' => $friend],
        ]);
    }
    

    public function Typing(Request $request){
        $request->validate(['friendId' => 'required|integer', 'value' => 'nullable|string|max:5000']);
        $request->user()->update(['last_seen_at' => now()]);
        $this->broadcastSafely(fn () => event(new userTyping([
           'value'=>$request->value,
           'userId'=> $request->user()->id,
           'friendId'=> $request->friendId,
           'userName'=>$request->userName,
           'lastName'=>$request->lastName,
           'typing'=>$request->typing
        ])));
    }

    /** Собеседники для списка «Сообщения»: только те, с кем есть переписка (не все пользователи сайта) */
    public function getAllUser(Request $request){
        $me = $request->user()->id;
        $friendIds = messageUser::where('owner_id', $me)->pluck('friend_id')
            ->merge(messageUser::where('friend_id', $me)->pluck('owner_id'))
            ->unique();

        $alluser = Owner::whereIn('id', $friendIds)->get()->map(function($user){
            $lastSeen = $user->last_seen_at ? Carbon::parse($user->last_seen_at):null;
            $isOnline = false;
            if ($lastSeen) {
                // сайт отмечает присутствие раз в минуту (app.vue) — запас на задержку
                $isOnline = $lastSeen->greaterThan(now()->subSeconds(90));
            }
            return [
                'id'=>$user->id,
                'name'=>$user->name,
                'last_name'=>$user->last_name,
                'avatar'=>$user->avatar,
                'phone_number'=>$user->phone_number,
                'online'=>$isOnline,
                'last_seen_at'=>$lastSeen?$lastSeen->diffForHumans():null,
                'uniq_id'=>$user->uniq_id,
                'role'=>$user->role, // значок «Администрация» в списке

            ];
        });
        return [$alluser];
    }
}
