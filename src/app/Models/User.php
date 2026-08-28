<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Пользователь кабинета с ролью администратора или сотрудника.
 */
#[Fillable(['name', 'email', 'password', 'role', 'api_token'])]
#[Hidden(['password', 'remember_token', 'api_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Определяет, обладает ли пользователь ролью администратора. */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Возвращает записи, назначенные сотруднику. */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'employee_id');
    }

    /**
     * Возвращает правила преобразования атрибутов модели.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
