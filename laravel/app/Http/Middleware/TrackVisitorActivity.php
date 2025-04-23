<?php

namespace App\Http\Middleware;

use App\Models\VisitorUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitorActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $agent = $request->header('user-Agent');
        $path = $request->path();
        $fullUrl = $request->fullUrl();
        $referer = $request->header('referer');
        VisitorUser::firstOrCreate([
            'ip-adress'=>$ip,
            'user-agent'=>$agent,
            'path'=>$path,
            'referer'=>$referer,
            'fullUrl'=>$fullUrl,
            'visited_at'=>now()
        ]);
        return $next($request);
    }
}
