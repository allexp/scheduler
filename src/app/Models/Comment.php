<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Пользовательский комментарий к клиенту или записи.
 */
class Comment extends Model
{
    use Auditable;

    protected $fillable = ['user_id', 'commentable_type', 'commentable_id', 'body'];

    /** Возвращает автора комментария. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Возвращает сущность, к которой относится комментарий. */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
