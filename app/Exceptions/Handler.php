<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Handle all uncaught exceptions as JSON
        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $this->handleApiException($request, $e);
            }
        });
    }

    /**
     * Convert an exception into a JSON response for API requests.
     */
    protected function handleApiException($request, Throwable $e): JsonResponse
    {
        // Validation exceptions — return 422
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => $e->validator->errors()->first(),
                'errors' => $e->validator->errors(),
            ], 422);
        }

        // Authentication exceptions — return 401
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // 404 Not Found
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'The requested resource was not found.',
            ], 404);
        }

        // Other HTTP exceptions (400, 403, 405, etc.)
        if ($e instanceof HttpExceptionInterface) {
            $statusCode = $e->getStatusCode();

            return response()->json([
                'message' => $e->getMessage() ?: $this->getDefaultMessage($statusCode),
            ], $statusCode);
        }

        // Generic server error — 500
        // Log the full exception in non-production for debugging
        if (config('app.debug')) {
            return response()->json([
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ], 500);
        }

        return response()->json([
            'message' => 'Internal server error.',
        ], 500);
    }

    /**
     * Get a default message for a given HTTP status code.
     */
    protected function getDefaultMessage(int $code): string
    {
        return match ($code) {
            400 => 'Bad request.',
            403 => 'Forbidden.',
            405 => 'Method not allowed.',
            422 => 'Validation failed.',
            429 => 'Too many requests.',
            500 => 'Internal server error.',
            503 => 'Service unavailable.',
            default => 'An error occurred.',
        };
    }
}
