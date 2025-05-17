<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    /**
     * Обработка входящий запроса.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Захардкожежный API-токен для демонстрационных целей
        // В реальном приложении его необходимо сохранять в безопасном месте
        $validToken = 'stab-app-secret-token';

        $token = $request->header('Authorization');

        if (str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }

        if ($token !== $validToken) {
            return response()->json(['error' => 'Unauthorized. Invalid API token.'], 401);
        }

        return $next($request);
    }
}
