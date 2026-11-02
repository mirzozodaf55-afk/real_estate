<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Заблокированный пользователь не может ничего делать от своего имени.
 * Токены при блокировке и так удаляются — это вторая линия защиты (например, токен выдан в момент блокировки).
 */
class EnsureNotBlocked
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && $user->isBlocked()) {
            return response()->json(['message' => $user->blockedMessage(), 'blocked' => true], 403);
        }
        return $next($request);
    }
}
