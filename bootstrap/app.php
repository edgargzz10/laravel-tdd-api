<?php

use App\Http\Middleware\ForceJsonResponseMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function Laravel\Prompts\error;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            ForceJsonResponseMiddleware::class
        ]);
        $middleware->alias([
            'role' => RoleMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $throwable) {
            return jsonResponse(message: $throwable->getMessage(), status: 422, errors: $throwable->errors());
        });

        $exceptions->render(function (AuthenticationException $throwable) {
            return jsonResponse(status: 401, message: $throwable->getMessage());
        });

        $exceptions->render(function (NotFoundHttpException $throwable) {
            return jsonResponse(status: 404, message: $throwable->getMessage());
        });


    })->create();
