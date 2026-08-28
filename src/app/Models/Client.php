<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Карточка клиента, содержащая контактные данные и историю взаимодействий.
 */
class Client extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'birthday',
        'notes',
        'created_by',
    ];

    protected $appends = ['full_name'];

    /**
     * Возвращает правила преобразования атрибутов модели.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['birthday' => 'date'];
    }

    /** Возвращает полное имя клиента. */
    public function getFullNameAttribute(): string
    {
        return trim($this->last_name.' '.$this->first_name);
    }

    /** Возвращает записи клиента. */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** Возвращает комментарии к карточке клиента. */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
