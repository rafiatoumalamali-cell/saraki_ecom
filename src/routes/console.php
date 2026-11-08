<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Unpaid orders hold stock; give it back once the window has closed
|--------------------------------------------------------------------------
*/
Schedule::command('orders:release-expired')
    ->everyFiveMinutes()
    ->withoutOverlapping();
