<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Always respond with JSON for /api/* requests — there's no "login"
     * route to redirect to (auth here is Filament's panel login), so an
     * unauthenticated API request would otherwise crash instead of
     * returning a clean 401 when the client doesn't set Accept: application/json.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('api/*')) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }

        return parent::unauthenticated($request, $exception);
    }
}
