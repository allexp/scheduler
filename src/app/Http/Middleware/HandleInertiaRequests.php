<?php

namespace App\Http\Middleware;

use App\Models\AppNotification;
use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Передаёт общие данные всем Inertia-страницам приложения.
 */
class HandleInertiaRequests extends Middleware
{
    /** Корневой Blade-шаблон приложения. */
    protected $rootView = 'app';

    /** Возвращает версию frontend-ресурсов. */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Возвращает свойства, доступные на каждой странице.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => fn () => $request->user()?->only(['id', 'name', 'email', 'role']),
            ],
            'unreadNotificationsCount' => fn () => $request->user()
                ? AppNotification::where('user_id', $request->user()->id)->whereNull('read_at')->count()
                : 0,
        ];
    }
}
