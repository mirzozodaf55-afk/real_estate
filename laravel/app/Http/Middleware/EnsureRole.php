<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Пускает только пользователей с одной из ролей: ->middleware('role:moderator,admin') */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();
        if (!$user || !$user->hasRole(...$roles)) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }
        return $next($request);
    }
}
