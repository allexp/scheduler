<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendAppointmentNotification;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * Управляет календарём записей клиентов.
 */
class AppointmentController extends Controller
{
    /** Возвращает записи для календаря или постраничного табличного списка. */
    public function index(Request $request): Collection|LengthAwarePaginator
    {
        $filters = $request->validate([
            'start' => 'nullable|date',
            'end' => 'nullable|date',
            'date' => 'nullable|date',
            'search' => 'nullable|string|max:100',
            'employee_id' => 'nullable|integer|exists:users,id',
            'paginate' => 'nullable|boolean',
        ]);

        $query = Appointment::with(['client:id,first_name,last_name,phone', 'employee:id,name'])
            ->when($request->start, fn ($query) => $query->where('starts_at', '>=', $request->start))
            ->when($request->end, fn ($query) => $query->where('starts_at', '<=', $request->end))
            ->when($request->date, fn ($query) => $query->whereDate('starts_at', $request->date))
            ->when($request->search, function ($query, string $search): void {
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
            ->when(
                $request->employee_id,
                fn ($query) => $query->where('employee_id', $request->employee_id),
            )
            ->orderBy('starts_at');

        return ($filters['paginate'] ?? false) ? $query->paginate(25) : $query->get();
    }

    /** Создаёт новую запись и ставит уведомление в очередь. */
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->ensureAvailable($data);
        $item = Appointment::create($data + ['created_by' => $request->user()->id]);
        SendAppointmentNotification::dispatch($item->id, 'created');
        Cache::forget('dashboard:stats');

        return response()->json($item->load(['client', 'employee']), 201);
    }

    /** Возвращает запись вместе со связанными данными. */
    public function show(Appointment $appointment)
    {
        return $appointment->load(['client', 'employee', 'comments.user:id,name']);
    }

    /** Обновляет запись и ставит уведомление в очередь. */
    public function update(Request $request, Appointment $appointment)
    {
        $data = $this->validated($request, true);
        $this->ensureAvailable($data + $appointment->only(['employee_id', 'starts_at', 'ends_at']), $appointment->id);
        $appointment->update($data);
        SendAppointmentNotification::dispatch($appointment->id, 'updated');
        Cache::forget('dashboard:stats');

        return $appointment->fresh()->load(['client', 'employee']);
    }

    /** Удаляет запись из календаря. */
    public function destroy(Appointment $appointment)
    {
        $id = $appointment->id;
        $appointment->delete();
        Cache::forget('dashboard:stats');

        return response()->json(['deleted' => $id]);
    }

    /**
     * Проверяет и возвращает данные записи.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, bool $partial = false): array
    {
        $p = $partial ? 'sometimes' : 'required';
        $data = $request->validate([
            'client_id' => "$p|exists:clients,id",
            'employee_id' => "$p|exists:users,id",
            'service' => "$p|string|max:255",
            'starts_at' => "$p|date",
            'ends_at' => "$p|date",
            'status' => 'sometimes|in:scheduled,completed,cancelled,no_show',
            'notes' => 'nullable|string|max:5000',
        ]);

        if (isset($data['starts_at'], $data['ends_at']) && strtotime($data['ends_at']) <= strtotime($data['starts_at'])) {
            throw ValidationException::withMessages(['ends_at' => ['Время окончания должно быть позже начала.']]);
        }

        return $data;
    }

    /**
     * Проверяет отсутствие пересекающейся записи у сотрудника.
     *
     * @param  array<string, mixed>  $data
     */
    private function ensureAvailable(array $data, ?int $except = null): void
    {
        $overlap = Appointment::where('employee_id', $data['employee_id'])
            ->where('status', 'scheduled')
            ->when($except, fn ($query) => $query->where('id', '!=', $except))
            ->where('starts_at', '<', $data['ends_at'])
            ->where('ends_at', '>', $data['starts_at'])
            ->exists();
        if ($overlap) {
            throw ValidationException::withMessages(['starts_at' => ['У сотрудника уже есть запись в это время.']]);
        }
    }
}
