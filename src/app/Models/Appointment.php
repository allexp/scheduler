<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Запись клиента к сотруднику на выбранный временной интервал.
 */
class Appointment extends Model
{
    use Auditable;

    protected $fillable = [
        'client_id',
        'employee_id',
        'created_by',
        'service',
        'starts_at',
        'ends_at',
        'status',
        'notes',
    ];

    /**
     * Возвращает правила преобразования атрибутов модели.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    /** Возвращает клиента, которому принадлежит запись. */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** Возвращает сотрудника, назначенного на запись. */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /** Возвращает комментарии к записи. */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
