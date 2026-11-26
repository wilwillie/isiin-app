<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('orders:mark-expired-pickups')
            ->dailyAt('00:05')
            ->timezone(config('isiin.display_timezone', 'Asia/Jakarta'));
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: ['midtrans/notifications']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
