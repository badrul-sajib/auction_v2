<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/signin');
        $middleware->alias([
            'ip.whitelist' => \App\Http\Middleware\EnsureIpWhitelisted::class,
        ]);

        // If this app runs behind a reverse proxy / CDN (nginx, Cloudflare, load balancer),
        // enable trusted proxies so request()->ip() reflects the real client IP (needed for
        // the IP whitelist). Replace '*' with your proxy's actual IPs in production —
        // trusting '*' on a directly-served app lets clients spoof X-Forwarded-For.
        // $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
