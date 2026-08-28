<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Заполняет приложение демонстрационными клиентами и записями.
 */
class DemoBookingSeeder extends Seeder
{
    /** Выполняет заполнение демонстрационными данными. */
    public function run(): void
    {
        $employee = User::where('email', 'employee@example.com')->first();

        if (! $employee) {
            throw new RuntimeException('Сначала необходимо создать тестового сотрудника.');
        }

        $clients = [
            ['Анна', 'Иванова', '+79990000001', 'anna.ivanova@example.com'],
            ['Михаил', 'Смирнов', '+79990000002', 'mikhail.smirnov@example.com'],
            ['Елена', 'Кузнецова', '+79990000003', 'elena.kuznetsova@example.com'],
            ['Алексей', 'Попов', '+79990000004', 'alexey.popov@example.com'],
            ['Мария', 'Васильева', '+79990000005', 'maria.vasileva@example.com'],
            ['Дмитрий', 'Петров', '+79990000006', 'dmitry.petrov@example.com'],
            ['Ольга', 'Соколова', '+79990000007', 'olga.sokolova@example.com'],
            ['Сергей', 'Михайлов', '+79990000008', 'sergey.mikhailov@example.com'],
            ['Наталья', 'Новикова', '+79990000009', 'natalia.novikova@example.com'],
            ['Игорь', 'Фёдоров', '+79990000010', 'igor.fedorov@example.com'],
            ['Татьяна', 'Морозова', '+79990000011', 'tatiana.morozova@example.com'],
            ['Андрей', 'Волков', '+79990000012', 'andrey.volkov@example.com'],
        ];

        $clientModels = collect($clients)->map(function (array $client) use ($employee): Client {
            return Client::updateOrCreate(
                ['email' => $client[3]],
                [
                    'first_name' => $client[0],
                    'last_name' => $client[1],
                    'phone' => $client[2],
                    'notes' => 'Демонстрационный клиент для локальной разработки.',
                    'created_by' => $employee->id,
                ],
            );
        });

        $services = [
            'Первичная консультация',
            'Повторная консультация',
            'Диагностика',
            'Плановый приём',
        ];
        $statuses = ['scheduled', 'scheduled', 'scheduled', 'completed', 'cancelled'];
        $weekStart = CarbonImmutable::now()->startOfWeek();

        for ($index = 0; $index < 20; $index++) {
            $dayOffset = $index % 10;
            $hour = 9 + (($index * 2) % 8);
            $startsAt = $weekStart->addDays($dayOffset)->setTime($hour, 0);

            Appointment::updateOrCreate(
                [
                    'client_id' => $clientModels[$index % $clientModels->count()]->id,
                    'starts_at' => $startsAt,
                ],
                [
                    'employee_id' => $employee->id,
                    'created_by' => $employee->id,
                    'service' => $services[$index % count($services)],
                    'ends_at' => $startsAt->addHour(),
                    'status' => $statuses[$index % count($statuses)],
                    'notes' => 'Тестовая запись, созданная демонстрационным сидером.',
                ],
            );
        }
    }
}
