<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The visitor timezone cookie is written by JavaScript, so it is not encrypted.
        $middleware->encryptCookies(except: ['tz']);
        $middleware->append(SecurityHeaders::class);
        $proxies = env('TRUSTED_PROXIES');
        $middleware->trustProxies(at: match (true) {
            blank($proxies) => null,
            $proxies === '*' => '*',
            default => array_map('trim', explode(',', $proxies)),
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

/*
 * Shared hosting (DirectAdmin / cPanel): the application lives outside the web root and the
 * domain's public_html folder sits next to it. When that layout is detected, public_html is the
 * public path, so built assets, uploads (storage link) and the Vite manifest resolve correctly.
 */
$sharedHostPublicPath = dirname(__DIR__, 2).'/public_html';

if (is_file($sharedHostPublicPath.'/index.php')) {
    $app->usePublicPath($sharedHostPublicPath);
}

return $app;
