<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware; // Pastikan ini di-import
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Routing\Middleware\SubstituteBindings; // Contoh middleware yang mungkin Anda butuhkan

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckUserRole::class,
        ]);

        $middleware->web(append: [

        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();