<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ограничивает доступ к маршруту перечисленными ролями.
 */
class RequireRole
{
    /** Проверяет наличие у пользователя одной из разрешённых ролей. */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        abort_unless($request->user() && in_array($request->user()->role, $roles, true), 403, 'Недостаточно прав.');

        return $next($request);
    }
}
