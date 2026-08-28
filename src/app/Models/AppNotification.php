<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Внутреннее уведомление пользователя приложения.
 */
class AppNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'payload',
        'read_at',
    ];

    /**
     * Возвращает правила преобразования атрибутов модели.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['payload' => 'array', 'read_at' => 'datetime'];
    }
}
