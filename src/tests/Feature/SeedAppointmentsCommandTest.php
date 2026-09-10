<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class SeedAppointmentsCommandTest extends TestCase
{
    use RefreshDatabase;

    /** Проверяет количество, диапазон дат и отсутствие пересечений записей. */
    public function test_command_creates_non_overlapping_appointments_for_each_day(): void
    {
        CarbonImmutable::setTestNow('2026-09-10 12:00:00');

        try {
            $employee = User::factory()->create([
                'email' => 'employee@example.com',
                'role' => 'employee',
            ]);
            Client::query()->create([
                'first_name' => 'Иван',
                'last_name' => 'Петров',
                'created_by' => $employee->id,
            ]);

            $this->artisan('db:seed-appointments')->assertSuccessful();

            $appointments = Appointment::query()->orderBy('starts_at')->get();
            $today = CarbonImmutable::today();

            for ($dayOffset = -10; $dayOffset <= 20; $dayOffset++) {
                $date = $today->addDays($dayOffset)->toDateString();
                $appointmentsCount = $appointments
                    ->filter(fn (Appointment $appointment): bool => $appointment->starts_at->toDateString() === $date)
                    ->count();

                $this->assertGreaterThanOrEqual(5, $appointmentsCount);
                $this->assertLessThanOrEqual(15, $appointmentsCount);
            }

            $this->assertTrue(
                $appointments->zip($appointments->skip(1))->every(
                    fn (Collection $pair): bool => $pair[1] === null || $pair[0]->ends_at->lte($pair[1]->starts_at),
                ),
            );
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    /** Проверяет понятную ошибку при отсутствии исходных данных. */
    public function test_command_fails_when_employee_and_clients_are_missing(): void
    {
        $this->artisan('db:seed-appointments')
            ->expectsOutput('Не найден сотрудник. Сначала выполните основное заполнение базы данных.')
            ->assertFailed();

        $this->assertDatabaseCount('appointments', 0);
    }
}
