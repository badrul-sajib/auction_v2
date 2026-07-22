<?php

namespace App\Http\Middleware;

use App\Models\AllowedIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIpWhitelisted
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! AllowedIp::allows($request->ip())) {
            return response()->view('pages.order.blocked', [], 403);
        }

        return $next($request);
    }
}
