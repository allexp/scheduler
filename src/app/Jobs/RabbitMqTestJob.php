<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Тестовое задание для проверки обработки сообщений RabbitMQ.
 */
class RabbitMqTestJob implements ShouldQueue
{
    use Queueable;

    /** Создаёт тестовое задание с переданным сообщением. */
    public function __construct(public readonly string $message) {}

    /** Записывает результат обработки задания в журнал приложения. */
    public function handle(): void
    {
        Log::info('RabbitMQ job processed', [
            'message' => $this->message,
            'processed_at' => now()->toIso8601String(),
        ]);
    }
}
