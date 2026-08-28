<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;

/**
 * Управляет внутренними уведомлениями пользователя.
 */
class NotificationController extends Controller
{
    /** Возвращает уведомления текущего пользователя. */
    public function index(Request $request)
    {
        return AppNotification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(30);
    }

    /** Помечает выбранное уведомление как прочитанное. */
    public function read(Request $request, AppNotification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $notification->update(['read_at' => now()]);

        return $notification;
    }
}
