<?php

namespace App\Exceptions;

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
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') && $e instanceof \Illuminate\Http\Exceptions\PostTooLargeException) {
            return response()->json([
                'message' => 'Файл слишком большой. Максимальный размер файла: ' . ini_get('post_max_size') . '.',
                'error' => 'file_too_large',
            ], 413);
        }

        // Без этого $request->validate() на API делает redirect()->back() вместо JSON 422
        if ($request->is('api/*') && $e instanceof \Illuminate\Validation\ValidationException) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], $e->status);
        }

        if ($request->is('api/*') && $e instanceof \Illuminate\Auth\AuthenticationException) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // 429 (throttle), 404, 403 и прочие HTTP-ошибки API — тоже JSON, а не HTML-страница
        if ($request->is('api/*') && $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $message = $status === 429 ? 'Слишком много запросов, подождите минуту' : ($e->getMessage() ?: 'Ошибка запроса');
            return response()->json(['message' => $message], $status, $e->getHeaders());
        }

        return parent::render($request, $e);
    }
}
