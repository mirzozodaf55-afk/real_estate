<?php

namespace App\Http\Controllers;

use App\Mail\LoginCodeMail;
use App\Models\Announ;
use App\Models\Owner;
use App\Models\DeletedAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CreateOwnerController extends Controller
{
    // Шаг 1: отправляем одноразовый код на почту. Ни код, ни токен в ответ НЕ возвращаются.
    public function createOwner(Request $request){
        $data = $request->validate(['email' => 'required|email:rfc|max:255']);
        $email = mb_strtolower(trim($data['email']));

        $existing = Owner::where('email', $email)->first();
        if ($existing?->isBlocked()) {
            return response()->json(['message' => $existing->blockedMessage()], 403);
        }
        // месяц после удаления аккаунта на эту почту новый не заводится — и код даже не отправляем
        if (!$existing && ($until = DeletedAccount::blockedUntil($email))) {
            return response()->json(['message' => DeletedAccount::message($until)], 403);
        }

        $code = strval(random_int(100000, 999999));
        // Код живёт только на сервере, 10 минут; счётчик попыток сбрасывается с каждым новым кодом
        Cache::put("login_code:{$email}", $code, now()->addMinutes(10));
        Cache::forget("login_attempts:{$email}");

        try {
            Mail::to($email)->send(new LoginCodeMail($code));
        } catch (\Throwable $e) {
            Log::error('Не удалось отправить код входа', ['email' => $email, 'error' => $e->getMessage()]);
            Cache::forget("login_code:{$email}");
            return response()->json(['message' => 'Не удалось отправить письмо. Попробуйте позже.'], 500);
        }

        return response()->json(['message' => 'Код отправлен на почту']);
    }

    // Шаг 2: проверяем код и только теперь создаём пользователя (если новый) и выдаём токен.
    public function verifyUser(Request $request){
        $data = $request->validate([
            'email' => 'required|email:rfc|max:255',
            'code' => 'required|digits:6',
        ]);
        $email = mb_strtolower(trim($data['email']));

        $stored = Cache::get("login_code:{$email}");
        if (!$stored) {
            return response()->json(['message' => 'Код истёк или не запрашивался. Запросите новый.'], 422);
        }

        // Не больше 5 попыток на один код — иначе 6 цифр перебираются
        $attempts = Cache::increment("login_attempts:{$email}");
        if ($attempts > 5) {
            Cache::forget("login_code:{$email}");
            return response()->json(['message' => 'Слишком много попыток. Запросите новый код.'], 429);
        }

        if (!hash_equals($stored, (string) $data['code'])) {
            return response()->json(['message' => 'Неверный код'], 422);
        }

        Cache::forget("login_code:{$email}");
        Cache::forget("login_attempts:{$email}");

        $owner = Owner::where('email', $email)->first();
        if ($owner?->isBlocked()) {
            // заблокировали, пока пользователь вводил код
            return response()->json(['message' => $owner->blockedMessage()], 403);
        }
        if (!$owner && ($until = DeletedAccount::blockedUntil($email))) {
            return response()->json(['message' => DeletedAccount::message($until)], 403);
        }
        if (!$owner) {
            // uniq_id выдаётся один раз при создании (раньше перегенерировался при каждом входе и ломал ссылки на чаты)
            $owner = Owner::create([
                'email' => $email,
                'uniq_id' => strval(random_int(10000000, 99999999)),
            ]);
        }

        return response()->json([
            'token' => $owner->createToken('user')->plainTextToken,
            'user' => $owner,
        ]);
    }
    
    public function UpdateOwner(Request $request){
        // Редактировать можно только себя — пользователь берётся из токена, а не из присланного phone
        $owner = $request->user();
        $owner->update(['last_seen_at' => now()]);

             $owner->update([
                'name'=>$request->name,
                'last_name'=>$request->lastName,
                'birth_date'=>$request->date
                ]
             );
            
            return $owner;
    }

    public function getUser(Request $request){
        $user = $request->user();
        // $request->user()->update(['last_seen_at'=>now()]);
        
        // if ($request->user()->last_seen_at === null || now()->diffInSeconds($request->user()->last_seen_at) > 60) {
            $request->user()->update(['last_seen_at' => now()]);
        // }
        return $user;
    }


    public function SendMessage(Request $request){
        $owner = $request->user();
        $email = mb_strtolower(trim((string) $request->email));
        // email теперь логин — один адрес не может принадлежать двум аккаунтам
        if (Owner::where('email', $email)->where('id', '!=', $owner->id)->exists()) {
            return response()->json(['message' => 'Эта почта уже используется другим аккаунтом'], 422);
        }
        $owner->update(['email'=>$email]);
        return $owner;
    }

    public function getOwnerFromAnnoun(Request $request){
        $owner = Owner::WhereIn('id', $request->owner_id)->get();
        $announ  = Announ::WhereIn('owner_id',$request->owner_id)->get();
        
        return [$announ, $owner];
    } 
    
    
}
