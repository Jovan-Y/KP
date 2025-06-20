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
        // Ini adalah tempat Anda mendaftarkan middleware Anda
        // Contoh untuk middleware role yang kita buat:
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckUserRole::class,
        ]);

        // Anda juga bisa menambahkan middleware ke grup web
        $middleware->web(append: [
            // Contoh middleware lain yang ingin Anda tambahkan ke grup 'web'
            // \App\Http\Middleware\ExampleWebMiddleware::class,
        ]);

        // Jika Anda punya middleware global, bisa juga di sini
        // $middleware->append(\App\Http\Middleware\LogRequests::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();