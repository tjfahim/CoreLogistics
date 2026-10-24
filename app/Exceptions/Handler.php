<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        // Validation errors: let Laravel redirect back / return 422 JSON
        if ($e instanceof ValidationException) {
            return parent::render($request, $e);
        }

        if ($e instanceof AuthenticationException) {
            return $this->respond($request, 401, 'Please login to continue.', redirect()->route('login'));
        }

        // Session expired (CSRF)
        if ($e instanceof TokenMismatchException) {
            return $this->respond($request, 419, 'Your session expired. Please try again.', back()->withInput($request->except($this->dontFlash)));
        }

        // Upload bigger than post_max_size
        if ($e instanceof PostTooLargeException) {
            return $this->respond($request, 413, 'The uploaded file is too large.', back());
        }

        if ($e instanceof AuthorizationException) {
            return $this->showError($request, 403, 'You do not have permission to do this.');
        }

        if ($e instanceof HttpException) {
            $status = $e->getStatusCode();
            $msg = $e->getMessage() ?: null;
            return $this->showError($request, $status, $status === 403 ? 'You do not have permission to do this.' : $msg);
        }

        // Excel / PhpSpreadsheet / Maatwebsite problems anywhere in the system
        if ($e instanceof \PhpOffice\PhpSpreadsheet\Exception
            || $e instanceof \Maatwebsite\Excel\Exceptions\NoTypeDetectedException
            || $e instanceof \Maatwebsite\Excel\Validators\ValidationException) {
            return $this->respond($request, 422,
                'Excel error: the file could not be read. Please check the file and upload a valid .xlsx/.xls.',
                back()->withInput());
        }

        // Database errors in production: friendly message, no SQL leak
        if ($e instanceof QueryException && !config('app.debug')) {
            report($e);
            return $this->respond($request, 500, 'A database error occurred. Please check your data and try again.', back()->withInput($request->except($this->dontFlash)));
        }

        return parent::render($request, $e);
    }

    /** JSON for AJAX, otherwise flash message + redirect. */
    private function respond($request, int $status, string $message, $redirect)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }
        return $redirect->with('error', $message);
    }

    /** JSON for AJAX, otherwise the matching errors/{status} view. */
    private function showError($request, int $status, ?string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message ?: 'Error'], $status);
        }
        if (view()->exists("errors.$status")) {
            return response()->view("errors.$status", ['message' => $message], $status);
        }
        return response()->view('errors.generic', ['code' => $status, 'message' => $message], $status);
    }
}
