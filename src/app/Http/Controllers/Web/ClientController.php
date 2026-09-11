<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\ClientController as ApiClientController;
use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Адаптирует операции с клиентами к Inertia-переходам. */
class ClientController extends Controller
{
    /** Создаёт клиента и возвращает пользователя к списку. */
    public function store(Request $request, ApiClientController $controller): RedirectResponse
    {
        $controller->store($request);

        return back();
    }

    /** Обновляет клиента и закрывает его карточку. */
    public function update(Request $request, Client $client, ApiClientController $controller): RedirectResponse
    {
        $controller->update($request, $client);

        return to_route('clients.index');
    }
}
