<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Управляет карточками клиентов.
 */
class ClientController extends Controller
{
    /** Возвращает постраничный список клиентов с поиском. */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('search'));

        return Client::query()
            ->when($q, fn ($query) => $query->where(
                fn ($searchQuery) => $searchQuery
                    ->where('first_name', 'ilike', "%$q%")
                    ->orWhere('last_name', 'ilike', "%$q%")
                    ->orWhere('phone', 'ilike', "%$q%")
                    ->orWhere('email', 'ilike', "%$q%"),
            ))
            ->latest()
            ->paginate(20);
    }

    /** Создаёт новую карточку клиента. */
    public function store(Request $request)
    {
        $client = Client::create($this->validated($request) + ['created_by' => $request->user()->id]);
        Cache::forget('dashboard:stats');

        return response()->json($client, 201);
    }

    /** Возвращает карточку клиента со связанными данными. */
    public function show(Client $client)
    {
        return $client->load(['appointments.employee:id,name', 'comments.user:id,name']);
    }

    /** Обновляет карточку клиента. */
    public function update(Request $request, Client $client)
    {
        $client->update($this->validated($request, true));
        Cache::forget('dashboard:stats');

        return $client->fresh();
    }

    /** Удаляет карточку клиента. */
    public function destroy(Client $client)
    {
        $client->delete();
        Cache::forget('dashboard:stats');

        return response()->noContent();
    }

    /**
     * Проверяет и возвращает данные карточки клиента.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, bool $partial = false): array
    {
        $p = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'first_name' => "$p|string|max:100",
            'last_name' => "$p|string|max:100",
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'birthday' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
        ]);
    }
}
