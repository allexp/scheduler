<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Запись журнала изменений сущности приложения.
 */
class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
    ];

    /**
     * Возвращает правила преобразования атрибутов модели.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array'];
    }

    /** Возвращает пользователя, выполнившего изменение. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
