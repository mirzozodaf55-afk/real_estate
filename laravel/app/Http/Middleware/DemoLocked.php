<?php

namespace App\Http\Middleware;

use App\Support\Demo;
use Closure;
use Illuminate\Http\Request;

/**
 * В демо закрыто то, что ломает его другим посетителям: удаление и переименование демо-аккаунтов,
 * смена почты, роли и блокировки, справочники и оформление сайта.
 */
class DemoLocked
{
    public function handle(Request $request, Closure $next)
    {
        if (Demo::enabled()) {
            return response()->json(['message' => 'В демо-версии это действие отключено'], 403);
        }
        return $next($request);
    }
}
