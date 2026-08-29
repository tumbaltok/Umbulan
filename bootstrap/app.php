<?php

use App\Http\Middleware\CekAtasan;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsurePhoneIsVerified;
use App\Http\Middleware\PreventBackHistory;
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
        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);

        $middleware->alias([
            'atasan' => CekAtasan::class,
            'role' => CheckRole::class,
            'phone.verified' => EnsurePhoneIsVerified::class,
            'account.complete' => \App\Http\Middleware\EnsureAccountIsComplete::class,
            'prevent-back-history' => PreventBackHistory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->is('logout') || $request->routeIs('logout')) {
                    \Illuminate\Support\Facades\Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect('/login');
                }

                return redirect()->route('login')->with('warning', 'Sesi Anda telah kedaluwarsa. Silakan login kembali.');
            }
        });
    })->create();
