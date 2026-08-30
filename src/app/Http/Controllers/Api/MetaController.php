<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Предоставляет вспомогательные данные кабинета и административные операции.
 */
class MetaController extends Controller
{
    /** Возвращает список доступных сотрудников. */
    public function employees()
    {
        return User::select('id', 'name', 'email', 'role')->orderBy('name')->get();
    }

    /** Возвращает кешированные показатели рабочего пространства. */
    public function dashboard()
    {
        return Cache::remember('dashboard:stats', 60, fn () => [
            'clients' => Client::count(),
            'today' => Appointment::whereDate('starts_at', today())->count(),
            'upcoming' => Appointment::where('starts_at', '>=', now())
                ->where('status', 'scheduled')
                ->count(),
        ]);
    }

    /** Возвращает постраничную историю изменений. */
    public function history(Request $request)
    {
        return AuditLog::with('user:id,name')
            ->when(
                $request->type,
                fn ($query) => $query->where(
                    'auditable_type',
                    'App\\Models\\'.ucfirst($request->type),
                ),
            )
            ->latest()
            ->paginate(30);
    }

}
