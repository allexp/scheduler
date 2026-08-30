<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Управляет пользователями в административном разделе. */
class UserController extends Controller
{
    /** Возвращает список пользователей. */
    public function index()
    {
        return User::query()->select('id', 'name', 'email', 'role', 'created_at')->orderBy('name')->get();
    }

    /** Создаёт пользователя. */
    public function store(Request $request)
    {
        $user = User::create($request->validate($this->rules()));

        return response()->json($user, 201);
    }

    /** Обновляет пользователя. */
    public function update(Request $request, User $user)
    {
        $data = $request->validate($this->rules($user));
        if (($data['role'] ?? $user->role) !== 'admin') {
            $this->ensureAnotherAdminExists($user);
        }
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        $user->update($data);

        return $user->fresh()->only(['id', 'name', 'email', 'role', 'created_at']);
    }

    /** Удаляет пользователя. */
    public function destroy(Request $request, User $user): Response
    {
        if ($request->user()->is($user)) {
            throw ValidationException::withMessages(['user' => ['Нельзя удалить собственную учётную запись.']]);
        }
        $this->ensureAnotherAdminExists($user);
        $user->delete();

        return response()->noContent();
    }

    /** Возвращает правила проверки формы пользователя. */
    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in(['admin', 'employee'])],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /** Проверяет, что после операции в системе останется администратор. */
    private function ensureAnotherAdminExists(User $user): void
    {
        if ($user->isAdmin() && ! User::where('role', 'admin')->whereKeyNot($user->getKey())->exists()) {
            throw ValidationException::withMessages(['role' => ['В системе должен остаться хотя бы один администратор.']]);
        }
    }
}
