<?php

namespace App\Jobs;

use App\Models\AppNotification;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Формирует пользовательские уведомления об изменении записи.
 */
class SendAppointmentNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** Создаёт задание для указанной записи и события. */
    public function __construct(public int $appointmentId, public string $event) {}

    /** Обрабатывает задание, полученное из очереди RabbitMQ. */
    public function handle(): void
    {
        $appointment = Appointment::with(['client', 'employee'])->find($this->appointmentId);
        if (! $appointment) {
            return;
        }
        $recipients = User::where('role', 'admin')->orWhereKey($appointment->employee_id)->pluck('id')->unique();
        foreach ($recipients as $userId) {
            AppNotification::create([
                'user_id' => $userId,
                'type' => 'appointment.'.$this->event,
                'title' => $this->event === 'created' ? 'Новая запись' : 'Запись изменена',
                'message' => $appointment->client->full_name.' — '
                    .$appointment->service.' — '
                    .$appointment->starts_at->format('d.m.Y H:i'),
                'payload' => ['appointment_id' => $appointment->id],
            ]);
        }
    }
}
