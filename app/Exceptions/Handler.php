<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
        $this->renderable(function (Throwable $e, $request) {
            // Check both JSON expectation and API routes
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->handleApiException($e);
            }
        });
    }

    /**
     * Handle API exceptions
     */
    private function handleApiException(Throwable $e): JsonResponse
    {
        $response = [
            'message' => 'Server Error',
            'error_type' => 'server_error'
        ];

        // Handle specific exceptions
        switch (true) {
            case $e instanceof ValidationException:
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'error_type' => 'validation_error',
                    'errors' => $e->validator->errors()->toArray()
                ], 422);

            case $e instanceof AuthenticationException:
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'error_type' => 'authentication_error'
                ], 401);

            case $e instanceof NotFoundHttpException:
                return response()->json([
                    'message' => 'Resource not found.',
                    'error_type' => 'not_found'
                ], 404);

            case $e instanceof GiphyNotFoundException:
                return response()->json([
                    'message' => $e->getMessage(),
                    'error_type' => 'giphy_not_found'
                ], 404);

            case $e instanceof GiphySearchException:
                return response()->json([
                    'message' => $e->getMessage(),
                    'error_type' => 'giphy_search_error'
                ], 400);

            case $e instanceof GiphyApiException:
                return response()->json([
                    'message' => $e->getMessage(),
                    'error_type' => 'giphy_api_error'
                ], 500);

            case $e instanceof FavoriteOperationException:
                return response()->json([
                    'message' => $e->getMessage(),
                    'error_type' => 'favorite_operation_error'
                ], 400);

            case $e instanceof HttpException:
                return response()->json([
                    'message' => $e->getMessage() ?: 'Http Error',
                    'error_type' => 'http_error'
                ], $e->getStatusCode());
        }

        // Log unexpected errors
        report($e);

        // Return detailed error info in debug mode
        if (config('app.debug')) {
            return response()->json([
                'message' => $e->getMessage(),
                'error_type' => 'server_error',
                'error_id' => $this->generateErrorId(),
                'debug' => [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => collect($e->getTrace())->map(function ($trace) {
                        return collect($trace)->except(['args'])->toArray();
                    })->toArray()
                ]
            ], 500);
        }

        // Production error response
        return response()->json([
            'message' => 'An unexpected error occurred.',
            'error_type' => 'server_error',
            'error_id' => $this->generateErrorId()
        ], 500);
    }

    /**
     * Generate a unique error ID for tracking
     */
    private function generateErrorId(): string
    {
        return date('Ymd') . '-' . substr(uniqid(), -6);
    }
}

