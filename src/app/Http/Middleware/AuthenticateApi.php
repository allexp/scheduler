<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Авторизует API-запрос по Bearer-токену.
 */
class AuthenticateApi
{
    /** Проверяет токен и передаёт запрос следующему обработчику. */
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken();
        $user = $plain ? User::where('api_token', hash('sha256', $plain))->first() : null;
        if (! $user) {
            return response()->json(['message' => 'Необходима авторизация.'], 401);
        }
        auth()->setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
