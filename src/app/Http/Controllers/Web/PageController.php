<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

/** Формирует данные страниц браузерного кабинета. */
class PageController extends Controller
{
    /** Показывает календарь и сводные показатели. */
    public function calendar(Request $request): Response
    {
        return Inertia::render('CalendarPage', [
            'appointments' => Appointment::with(['client:id,first_name,last_name,phone', 'employee:id,name'])
                ->orderBy('starts_at')
                ->get(),
            'stats' => Cache::remember('dashboard:stats', 60, fn () => [
                'clients' => Client::count(),
                'today' => Appointment::whereDate('starts_at', today())->count(),
                'upcoming' => Appointment::where('starts_at', '>=', now())
                    ->where('status', 'scheduled')
                    ->count(),
            ]),
            'selectedAppointment' => $this->selectedAppointment($request),
            'clients' => fn () => Client::orderBy('last_name')->orderBy('first_name')->get(),
            'employees' => fn () => User::select('id', 'name', 'email', 'role')->orderBy('name')->get(),
        ]);
    }

    /** Показывает фильтруемый список записей. */
    public function appointments(Request $request): Response
    {
        $filters = $request->validate([
            'date' => ['nullable', 'date'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $appointments = Appointment::with(['client:id,first_name,last_name,phone', 'employee:id,name'])
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('starts_at', $date))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->whereHas('client', function ($clientQuery) use ($search): void {
                    $pattern = '%'.mb_strtolower($search).'%';
                    $clientQuery
                        ->whereRaw('lower(first_name) like ?', [$pattern])
                        ->orWhereRaw('lower(last_name) like ?', [$pattern])
                        ->orWhere('phone', 'like', $pattern)
                        ->orWhereRaw("lower(first_name || ' ' || last_name) like ?", [$pattern])
                        ->orWhereRaw("lower(last_name || ' ' || first_name) like ?", [$pattern]);
                });
            })
            ->orderBy('starts_at')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('AppointmentListPage', [
            'appointments' => $appointments,
            'filters' => [
                'date' => $filters['date'] ?? '',
                'search' => $filters['search'] ?? '',
            ],
        ]);
    }

    /** Показывает клиентов и выбранную карточку. */
    public function clients(Request $request): Response
    {
        return Inertia::render('ClientsPage', [
            'clients' => Client::latest()->get(),
            'selectedClient' => $this->selectedClient($request),
        ]);
    }

    /** Показывает форму создания записи. */
    public function createAppointment(): Response
    {
        return Inertia::render('AppointmentCreatePage', [
            'clients' => Client::orderBy('last_name')->orderBy('first_name')->get(),
            'employees' => User::select('id', 'name', 'email', 'role')->orderBy('name')->get(),
        ]);
    }

    /** Показывает уведомления текущего пользователя. */
    public function notifications(Request $request): Response
    {
        return Inertia::render('NotificationsPage', [
            'notifications' => AppNotification::where('user_id', $request->user()->id)
                ->latest()
                ->limit(30)
                ->get(),
        ]);
    }

    /** Показывает административный журнал изменений. */
    public function history(Request $request): Response
    {
        $type = $request->validate(['type' => ['nullable', 'string', 'max:100']])['type'] ?? null;

        return Inertia::render('HistoryPage', [
            'history' => AuditLog::with('user:id,name')
                ->when($type, fn ($query) => $query->where('auditable_type', 'App\\Models\\'.ucfirst($type)))
                ->latest()
                ->paginate(30)
                ->withQueryString(),
        ]);
    }

    /** Показывает административный список пользователей. */
    public function users(): Response
    {
        return Inertia::render('UsersPage', [
            'users' => User::select('id', 'name', 'email', 'role', 'created_at')->orderBy('name')->get(),
        ]);
    }

    /** Возвращает полную карточку выбранной записи. */
    private function selectedAppointment(Request $request): ?Appointment
    {
        if (! $request->filled('appointment')) {
            return null;
        }

        return Appointment::with(['client', 'employee', 'comments.user:id,name'])
            ->findOrFail($request->integer('appointment'));
    }

    /** Возвращает полную карточку выбранного клиента. */
    private function selectedClient(Request $request): ?Client
    {
        if (! $request->filled('client')) {
            return null;
        }

        return Client::with(['appointments.employee:id,name', 'comments.user:id,name'])
            ->findOrFail($request->integer('client'));
    }
}
