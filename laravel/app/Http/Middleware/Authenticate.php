<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // API никогда не редиректит на web-страницу логина: фронт шлёт обычный fetch без
        // Accept: application/json, и редирект на /login заканчивался CORS-ошибкой вместо 401.
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }
        return route('login');
    }
}
