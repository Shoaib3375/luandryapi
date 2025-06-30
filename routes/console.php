<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
//app(Schedule::class)->command('backup:run')->daily()->at('02:00');
//app(Schedule::class)->command('backup:clean')->daily()->at('02:30');
