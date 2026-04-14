<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     */
    public function report(Throwable $e): void
    {
        // Log exception with context
        if ($this->shouldReport($e)) {
            Log::error('Exception occurred', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::check() ? Auth::id() : null,
                'user_email' => Auth::check() ? Auth::user()->email : null,
                'url' => Request::url(),
                'method' => Request::method(),
                'ip' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Response
     */
    public function render($request, Throwable $e)
    {
        // Handle AJAX requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->renderApiException($request, $e);
        }

        // Handle specific exceptions with custom responses
        if ($e instanceof ModelNotFoundException) {
            return response()->view('errors.404', [], 404);
        }

        if ($e instanceof AuthorizationException) {
            return response()->view('errors.403', [], 403);
        }

        return parent::render($request, $e);
    }

    /**
     * Render an exception into a JSON response for API requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    protected function renderApiException($request, Throwable $e)
    {
        $statusCode = 500;

        if ($e instanceof ValidationException) {
            $statusCode = $e->status;
        } elseif ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
        }

        $response = [
            'error' => [
                'message' => $e instanceof ValidationException
                    ? 'The given data was invalid.'
                    : ($this->isHttpException($e) ? $e->getMessage() : 'Server Error'),
                'code' => $statusCode,
            ],
        ];

        // Add validation errors for ValidationException
        if ($e instanceof ValidationException) {
            $response['error']['validation'] = $e->errors();
        }

        // Don't expose trace in production
        if (app()->environment() !== 'production' && config('app.debug')) {
            $response['error']['trace'] = $e->getTraceAsString();
        }

        return response()->json($response, $statusCode);
    }
}
