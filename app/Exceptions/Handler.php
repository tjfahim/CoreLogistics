<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        // 401 / unauthenticated
        if ($e instanceof AuthenticationException) {
            return redirect()->route('login')
                ->with('error', 'Please login to continue.');
        }

        // Policy/gate denial
        if ($e instanceof AuthorizationException) {
            return redirect()->route('login')
                ->with('error', 'You do not have permission to access this page.');
        }

        // Any HttpException (403, 404, etc.)
        if ($e instanceof HttpException) {
            $status = $e->getStatusCode();

            if ($status === 403) {
                if (auth()->check()) {
                    auth()->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
                return redirect()->route('login')
                    ->with('error', 'Unauthorized. Please login with the correct account.');
            }

            // 404: if logged in, let Laravel render the 404 view (no redirect).
            // If guest, show the 404 view too — but the view will link to login.
            // No special handling needed here — the view handles auth state.
        }

        return parent::render($request, $e);
    }
}