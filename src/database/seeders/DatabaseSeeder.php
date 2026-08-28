<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Запускает базовое заполнение базы данных приложения.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /** Создаёт тестовых пользователей и демонстрационные данные. */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Администратор', 'role' => 'admin', 'password' => 'password'],
        );
        User::updateOrCreate(
            ['email' => 'employee@example.com'],
            ['name' => 'Сотрудник', 'role' => 'employee', 'password' => 'password'],
        );

        $this->call(DemoBookingSeeder::class);
    }
}
