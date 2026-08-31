<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Процесс `php artisan schedule:work` запускает генератор ежедневно в 00:05.
Schedule::command('app:generate-daily-appointments')->dailyAt('00:05');
