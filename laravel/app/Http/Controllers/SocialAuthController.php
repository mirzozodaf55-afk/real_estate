<?php

namespace App\Http\Controllers;

use App\Models\DeletedAccount;
use App\Models\Owner;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Вход через Google и Apple. Браузер получает токен у провайдера, мы его проверяем и выдаём свой (Sanctum) —
 * как после ввода кода из письма. Аккаунт — по почте: та же почта = тот же пользователь.
 */
class SocialAuthController extends Controller
{
    /** Что включено — фронт показывает только настроенные кнопки. Client ID публичны, секреты сюда не попадают */
    public function providers()
    {
        return response()->json([
            'google' => config('services.google.client_id') ?: null,
            'apple' => config('services.apple.client_id') ? [
                'client_id' => config('services.apple.client_id'),
                'redirect' => config('services.apple.redirect'),
            ] : null,
        ]);
    }

    public function google(Request $request)
    {
        $data = $request->validate(['access_token' => 'required|string|max:4096']);
        $clientId = config('services.google.client_id');
        if (!$clientId) {
            return response()->json(['message' => 'Вход через Google не настроен'], 503);
        }

        try {
            // Токен должен быть выдан именно нашему приложению — иначе чужой сайт мог бы подсунуть токен своего пользователя
            $info = $this->http()->get('https://oauth2.googleapis.com/tokeninfo', ['access_token' => $data['access_token']]);
            if (!$info->ok() || $info->json('aud') !== $clientId) {
                return response()->json(['message' => 'Google не подтвердил вход, попробуйте ещё раз'], 401);
            }
            $profile = $this->http()->withToken($data['access_token'])->get('https://openidconnect.googleapis.com/v1/userinfo');
        } catch (\Throwable $e) {
            Log::warning('Google недоступен', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Google сейчас недоступен, попробуйте позже'], 503);
        }

        if (!$profile->ok() || !$profile->json('email') || !$profile->json('email_verified')) {
            return response()->json(['message' => 'В аккаунте Google нет подтверждённой почты'], 422);
        }

        return $this->login($profile->json('email'), $profile->json('given_name'), $profile->json('family_name'));
    }

    public function apple(Request $request)
    {
        $data = $request->validate([
            'id_token' => 'required|string|max:8192',
            // имя Apple присылает только при первом входе и только браузеру
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
        ]);
        $clientId = config('services.apple.client_id');
        if (!$clientId) {
            return response()->json(['message' => 'Вход через Apple не настроен'], 503);
        }

        try {
            $keys = Cache::remember('apple_jwks', now()->addHours(6), fn () => $this->http()->get('https://appleid.apple.com/auth/keys')->throw()->json());
            $claims = JWT::decode($data['id_token'], JWK::parseKeySet($keys, 'RS256'));
        } catch (\Throwable $e) {
            Log::info('Токен Apple не прошёл проверку', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Apple не подтвердил вход, попробуйте ещё раз'], 401);
        }

        // подпись проверена (и срок действия — внутри JWT::decode); осталось убедиться, что токен для нас
        if (($claims->iss ?? null) !== 'https://appleid.apple.com' || ($claims->aud ?? null) !== $clientId || empty($claims->email)) {
            return response()->json(['message' => 'Apple не подтвердил вход, попробуйте ещё раз'], 401);
        }

        return $this->login($claims->email, $data['first_name'] ?? null, $data['last_name'] ?? null);
    }

    /** Те же правила, что и при входе по коду (CreateOwnerController::verifyUser) */
    private function login(string $email, ?string $firstName, ?string $lastName)
    {
        $email = mb_strtolower(trim($email));
        $owner = Owner::where('email', $email)->first();

        if ($owner?->isBlocked()) {
            return response()->json(['message' => $owner->blockedMessage()], 403);
        }
        if (!$owner && ($until = DeletedAccount::blockedUntil($email))) {
            return response()->json(['message' => DeletedAccount::message($until)], 403);
        }
        if (!$owner) {
            $owner = Owner::create([
                'email' => $email,
                'uniq_id' => strval(random_int(10000000, 99999999)),
                'name' => $firstName ? mb_substr($firstName, 0, 100) : null,
                'last_name' => $lastName ? mb_substr($lastName, 0, 100) : null,
            ]);
        } elseif (!$owner->name && $firstName) {
            // имя из Google/Apple подставляем, только если пользователь ещё не заполнил своё
            $owner->update(['name' => mb_substr($firstName, 0, 100), 'last_name' => $owner->last_name ?: ($lastName ? mb_substr($lastName, 0, 100) : null)]);
        }

        return response()->json([
            'token' => $owner->createToken('user')->plainTextToken,
            'user' => $owner,
        ]);
    }

    private function http()
    {
        // свой файл корневых сертификатов — как в OpenAi (PHP на Windows их не знает: cURL error 60)
        $ca = config('services.openai.ca_bundle');
        return Http::timeout(15)->acceptJson()->withOptions(['verify' => $ca && is_file($ca) ? $ca : true]);
    }
}
