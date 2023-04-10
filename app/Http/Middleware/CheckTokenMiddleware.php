<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('token') !== 'Gcc?!bxTEjitDTR7PNG8uakl-n?-ARZrsUj/Q!H91Xs4E96F5rRL6rO983DF!DwG') {
            return response()->json(['Error' => 'Token is invalid']);
        }
        return $next($request);
    }
}
