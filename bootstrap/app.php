<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\HandleInertiaRequests;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->statefulApi();
        $middleware->throttleApi();

        $middleware->alias([
            'Excel' => Maatwebsite\Excel\Facades\Excel::class,
            'type.ict_developer' => \App\Http\Middleware\IctDeveloperMiddleware::class,
            'type.ict_section_head' => \App\Http\Middleware\IctSectionHead::class,
            'type.ict_group_leader' => \App\Http\Middleware\IctGroupLeader::class,
            'type.ict_admin' => \App\Http\Middleware\IctAdmin::class,
            'type.ict_technician' => \App\Http\Middleware\IctTechnician::class,
            // 'type.ict_guest' => \App\Http\Middleware\AdminMiddleware::class,
            'checkRole' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if ($request->is('/')) {
                return redirect('/login');
            }
        });
    })->create();
