<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Support\Demo;
use Illuminate\Http\Request;

/** Вход в демо одной кнопкой — без почты и кода (на бесплатном хостинге письма всё равно не уходят) */
class DemoController extends Controller
{
    public function login(Request $request)
    {
        abort_unless(Demo::enabled(), 404);
        $data = $request->validate(['role' => 'required|in:' . implode(',', array_keys(Demo::ACCOUNTS))]);

        $owner = Owner::where('email', Demo::ACCOUNTS[$data['role']])->first();
        abort_unless($owner, 503, 'Демо-данные ещё готовятся, обновите страницу через минуту');

        return response()->json([
            'token' => $owner->createToken('demo')->plainTextToken,
            'user' => $owner,
        ]);
    }
}
