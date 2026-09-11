<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Адаптирует управление пользователями к Inertia-переходам. */
class UserController extends Controller
{
    /** Создаёт пользователя и возвращает обновлённый список. */
    public function store(Request $request, ApiUserController $controller): RedirectResponse
    {
        $controller->store($request);

        return back();
    }

    /** Обновляет пользователя и возвращает обновлённый список. */
    public function update(Request $request, User $user, ApiUserController $controller): RedirectResponse
    {
        $controller->update($request, $user);

        return back();
    }

    /** Удаляет пользователя и возвращает обновлённый список. */
    public function destroy(Request $request, User $user, ApiUserController $controller): RedirectResponse
    {
        $controller->destroy($request, $user);

        return back();
    }
}
