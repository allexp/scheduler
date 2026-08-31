<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateDailyAppointmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_twenty_clients_and_appointments_on_unique_dates(): void
    {
        User::factory()->create(['role' => 'employee']);

        $this->artisan('app:generate-daily-appointments')
            ->expectsOutput('Создано 20 клиентов и 20 записей.')
            ->assertSuccessful();

        $this->assertDatabaseCount('clients', 20);
        $this->assertDatabaseCount('appointments', 20);
        $this->assertSame(20, Appointment::query()->distinct()->count('starts_at'));
    }

    public function test_command_fails_without_an_employee(): void
    {
        $this->artisan('app:generate-daily-appointments')
            ->expectsOutput('Не найден сотрудник для назначения записей.')
            ->assertFailed();

        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseCount('appointments', 0);
    }
}
