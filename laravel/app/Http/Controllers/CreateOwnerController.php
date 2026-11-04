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
        $data = $request->validate([
            'name' => 'nullable|string|max:60',
            'lastName' => 'nullable|string|max:60',
            'date' => 'nullable|date|before:today|after:1900-01-01',
        ], ['date.*' => 'Проверьте дату рождения']);

        $owner->update([
            'name' => $data['name'] ?? null,
            'last_name' => $data['lastName'] ?? null,
            'birth_date' => $data['date'] ?? null,
            'last_seen_at' => now(),
        ]);

        return $owner;
    }

    public function getUser(Request $request){
        $user = $request->user();
        // «в сети» (порог 90 с, MessageController::getAllUser): пишем в базу не чаще раза в 30 с, а не на каждый запрос
        if (!$user->last_seen_at || $user->last_seen_at->lt(now()->subSeconds(30))) {
            $user->update(['last_seen_at' => now()]);
        }
        return $user;
    }


    /**
     * Смена почты в профиле — в два шага, как вход: сначала код на новую почту, потом {email, code}.
     * Почта — это логин: без подтверждения можно было записать себе чужой адрес и потом получить доступ к аккаунту его хозяина.
     */
    public function SendMessage(Request $request){
        $owner = $request->user();
        $data = $request->validate([
            'email' => 'required|email:rfc|max:255',
            'code' => 'nullable|digits:6',
        ], ['email.*' => 'Введите корректную почту']);
        $email = mb_strtolower(trim($data['email']));
        // email — логин: один адрес не может принадлежать двум аккаунтам
        if (Owner::where('email', $email)->where('id', '!=', $owner->id)->exists()) {
            return response()->json(['message' => 'Эта почта уже используется другим аккаунтом'], 422);
        }
        if ($email === $owner->email) {
            return $owner;
        }

        $key = "email_change:{$owner->id}";
        if (empty($data['code'])) {
            $code = strval(random_int(100000, 999999));
            Cache::put($key, ['email' => $email, 'code' => $code], now()->addMinutes(10));
            Cache::forget("$key:attempts");
            try {
                Mail::to($email)->send(new LoginCodeMail($code, true));
            } catch (\Throwable $e) {
                Log::error('Не удалось отправить код смены почты', ['email' => $email, 'error' => $e->getMessage()]);
                Cache::forget($key);
                return response()->json(['message' => 'Не удалось отправить письмо. Попробуйте позже.'], 500);
            }
            return response()->json(['message' => 'Код отправлен на новую почту', 'pending' => true]);
        }

        $stored = Cache::get($key);
        if (!$stored || $stored['email'] !== $email) {
            return response()->json(['message' => 'Код истёк или не запрашивался. Запросите новый.'], 422);
        }
        if (Cache::increment("$key:attempts") > 5) {
            Cache::forget($key);
            return response()->json(['message' => 'Слишком много попыток. Запросите новый код.'], 429);
        }
        if (!hash_equals($stored['code'], (string) $data['code'])) {
            return response()->json(['message' => 'Неверный код'], 422);
        }
        Cache::forget($key);
        Cache::forget("$key:attempts");
        $owner->update(['email' => $email]);
        return $owner;
    }
    
    
}
