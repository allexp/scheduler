<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

#[Signature('db:seed-appointments')]
#[Description('Создаёт непересекающиеся записи за последние 10 и следующие 20 дней')]
class SeedAppointmentsCommand extends Command
{
    private const string SEED_NOTE = 'Тестовая запись, созданная командой db:seed-appointments.';

    private const int FIRST_SLOT_HOUR = 8;

    private const int SLOTS_PER_DAY = 15;

    private const int APPOINTMENT_DURATION_MINUTES = 45;

    /** Выполняет заполнение календаря тестовыми записями. */
    public function handle(): int
    {
        $employee = User::query()
            ->where('email', 'employee@example.com')
            ->orWhere('role', 'employee')
            ->first();
        $clients = Client::query()->get(['id']);

        if (! $employee) {
            $this->error('Не найден сотрудник. Сначала выполните основное заполнение базы данных.');

            return self::FAILURE;
        }

        if ($clients->isEmpty()) {
            $this->error('Не найдены клиенты. Сначала выполните основное заполнение базы данных.');

            return self::FAILURE;
        }

        try {
            $createdCount = DB::transaction(function () use ($employee, $clients): int {
                Appointment::query()->where('notes', self::SEED_NOTE)->delete();

                return $this->createAppointments($employee, $clients);
            });
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Создано записей: {$createdCount}.");

        return self::SUCCESS;
    }

    /**
     * Создаёт записи для каждой даты в заданном диапазоне.
     *
     * @param  Collection<int, Client>  $clients
     */
    private function createAppointments(User $employee, Collection $clients): int
    {
        $today = CarbonImmutable::today();
        $createdCount = 0;

        for ($dayOffset = -10; $dayOffset <= 20; $dayOffset++) {
            $date = $today->addDays($dayOffset);
            $availableSlots = $this->availableSlots($date);

            if ($availableSlots->count() < 5) {
                throw new RuntimeException(
                    "На дату {$date->toDateString()} доступно меньше пяти непересекающихся интервалов.",
                );
            }

            $appointmentsPerDay = random_int(5, min(15, $availableSlots->count()));

            foreach ($availableSlots->shuffle()->take($appointmentsPerDay) as $startsAt) {
                Appointment::create([
                    'client_id' => $clients->random()->id,
                    'employee_id' => $employee->id,
                    'created_by' => $employee->id,
                    'service' => $this->randomService(),
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->addMinutes(self::APPOINTMENT_DURATION_MINUTES),
                    'status' => $date->isBefore($today) ? $this->randomPastStatus() : 'scheduled',
                    'notes' => self::SEED_NOTE,
                ]);

                $createdCount++;
            }
        }

        return $createdCount;
    }

    /**
     * Возвращает свободные интервалы указанного дня.
     *
     * @return Collection<int, CarbonImmutable>
     */
    private function availableSlots(CarbonImmutable $date): Collection
    {
        $dayStart = $date->startOfDay();
        $dayEnd = $date->endOfDay();
        $busyAppointments = Appointment::query()
            ->where('starts_at', '<=', $dayEnd)
            ->where('ends_at', '>=', $dayStart)
            ->get(['starts_at', 'ends_at']);

        return collect(range(0, self::SLOTS_PER_DAY - 1))
            ->map(fn (int $slot): CarbonImmutable => $date
                ->setTime(self::FIRST_SLOT_HOUR + $slot, 0))
            ->reject(function (CarbonImmutable $startsAt) use ($busyAppointments): bool {
                $endsAt = $startsAt->addMinutes(self::APPOINTMENT_DURATION_MINUTES);

                return $busyAppointments->contains(
                    fn (Appointment $appointment): bool => $appointment->starts_at->lt($endsAt)
                        && $appointment->ends_at->gt($startsAt),
                );
            })
            ->values();
    }

    /** Возвращает случайное название услуги. */
    private function randomService(): string
    {
        return collect([
            'Первичная консультация',
            'Повторная консультация',
            'Диагностика',
            'Плановый приём',
        ])->random();
    }

    /** Возвращает случайный итоговый статус прошедшей записи. */
    private function randomPastStatus(): string
    {
        return collect(['completed', 'cancelled', 'no_show'])->random();
    }
}
