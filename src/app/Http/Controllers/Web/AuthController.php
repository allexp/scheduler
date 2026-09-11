<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/** Управляет сессионной авторизацией браузерного кабинета. */
class AuthController extends Controller
{
    /** Показывает форму входа. */
    public function createLogin(): Response
    {
        return Inertia::render('AuthPage', ['mode' => 'login']);
    }

    /** Авторизует пользователя и обновляет идентификатор сессии. */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);
        $remember = (bool) ($credentials['remember'] ?? false);
        unset($credentials['remember']);

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => ['Неверный email или пароль.'],
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('calendar'));
    }

    /** Показывает форму регистрации. */
    public function createRegistration(): Response
    {
        return Inertia::render('AuthPage', ['mode' => 'register']);
    }

    /** Регистрирует сотрудника и начинает пользовательскую сессию. */
    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $user = User::create($data + ['role' => 'employee']);

        Auth::login($user);
        $request->session()->regenerate();

        return to_route('calendar');
    }

    /** Завершает пользовательскую сессию. */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }
}
