<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

#[Signature('app:generate-daily-appointments')]
#[Description('Создаёт 20 случайных клиентов и записей на разные даты')]
class GenerateDailyAppointments extends Command
{
    /** Создаёт клиентов и записи на двадцать уникальных дат. */
    public function handle(): int
    {
        // Количество ближайших дней, среди которых выбираются даты записей.
        $days = 20;

        // Записи назначаются только существующим сотрудникам.
        $employees = User::query()->where('role', 'employee')->get();

        if ($employees->isEmpty()) {
            $this->error('Не найден сотрудник для назначения записей.');

            return self::FAILURE;
        }

        $services = [
            'Первичная консультация',
            'Повторная консультация',
            'Диагностика',
            'Плановый приём',
        ];
        // Выбранные дни не повторяются, поэтому записи создаются на разные даты.
        $dates = collect(range(1, $days))->random(20)->sort()->values();

        // Транзакция предотвращает сохранение неполного набора данных при ошибке.
        DB::transaction(function () use ($employees, $services, $dates): void {
            foreach ($dates as $dayOffset) {
                $employee = $employees->random();
                $startsAt = CarbonImmutable::today()
                    ->addDays($dayOffset)
                    ->setTime(random_int(9, 17), random_int(0, 1) * 30);
                $client = Client::create([
                    'first_name' => fake('ru_RU')->firstName(),
                    'last_name' => fake('ru_RU')->lastName(),
                    'phone' => fake('ru_RU')->unique()->numerify('+79#########'),
                    'email' => fake()->unique()->safeEmail(),
                    'birthday' => fake()->dateTimeBetween('-75 years', '-18 years')->format('Y-m-d'),
                    'created_by' => $employee->id,
                ]);

                Appointment::create([
                    'client_id' => $client->id,
                    'employee_id' => $employee->id,
                    'created_by' => $employee->id,
                    'service' => $services[array_rand($services)],
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->addHour(),
                    'status' => 'scheduled',
                    'notes' => 'Запись создана автоматическим ежедневным генератором.',
                ]);
            }
        });

        // Сводная статистика зависит от количества созданных записей.
        Cache::forget('dashboard:stats');
        $this->info('Создано 20 клиентов и 20 записей.');

        return self::SUCCESS;
    }
}
