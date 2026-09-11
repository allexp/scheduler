<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Api\AppointmentController as ApiAppointmentController;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Адаптирует операции с записями к Inertia-переходам. */
class AppointmentController extends Controller
{
    /** Создаёт запись и открывает календарь. */
    public function store(Request $request, ApiAppointmentController $controller): RedirectResponse
    {
        $controller->store($request);

        return to_route('calendar');
    }

    /** Обновляет запись и возвращается на предыдущую страницу. */
    public function update(
        Request $request,
        Appointment $appointment,
        ApiAppointmentController $controller,
    ): RedirectResponse {
        $controller->update($request, $appointment);

        return back();
    }
}
