<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Управляет регистрацией и авторизацией пользователей API.
 */
class AuthController extends Controller
{
    /** Регистрирует нового сотрудника и выдаёт токен доступа. */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user = User::create($data + ['role' => 'employee']);

        return response()->json($this->issueToken($user), 201);
    }

    /** Авторизует пользователя и выдаёт новый токен доступа. */
    public function login(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Неверный email или пароль.']]);
        }

        return $this->issueToken($user);
    }

    /** Возвращает текущего авторизованного пользователя. */
    public function me(Request $request)
    {
        return $request->user();
    }

    /** Завершает текущую пользовательскую сессию API. */
    public function logout(Request $request)
    {
        $request->user()->update(['api_token' => null]);

        return response()->noContent();
    }

    /**
     * Создаёт и сохраняет новый токен доступа пользователя.
     *
     * @return array{user: User, token: string}
     */
    private function issueToken(User $user): array
    {
        $plain = bin2hex(random_bytes(32));
        $user->update(['api_token' => hash('sha256', $plain)]);

        return ['user' => $user->fresh(), 'token' => $plain];
    }
}
