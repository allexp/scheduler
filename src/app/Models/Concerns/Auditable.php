<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Добавляет автоматическое журналирование изменений модели.
 */
trait Auditable
{
    /** Регистрирует обработчики событий аудируемой модели. */
    public static function bootAuditable(): void
    {
        static::created(fn ($model) => self::writeAudit($model, 'created', null, $model->getAttributes()));
        static::updated(fn ($model) => self::writeAudit($model, 'updated', $model->getOriginal(), $model->getChanges()));
        static::deleted(fn ($model) => self::writeAudit($model, 'deleted', $model->getOriginal(), null));
    }

    /**
     * Сохраняет одно событие изменения в журнале аудита.
     *
     * @param  array<string, mixed>|null  $old  Предыдущие значения атрибутов.
     * @param  array<string, mixed>|null  $new  Новые значения атрибутов.
     */
    private static function writeAudit(Model $model, string $action, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }
}
