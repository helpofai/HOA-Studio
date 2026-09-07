<?php

use App\Http\Middleware\AuthenticateStudioToken;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SecurityHeadersMiddleware::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/v1/wordpress/*',
        ]);

        // Trust proxies for HTTPS detection behind reverse proxy (cPanel, LiteSpeed, Cloudflare, Nginx)
        $middleware->trustProxies(at: '*', headers: 0b111111); // Set to 63 (all headers)

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'auth.studio' => AuthenticateStudioToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
