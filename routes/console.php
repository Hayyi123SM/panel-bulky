<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote')->hourly();

Schedule::command('app:auto-cancel-order')->everyMinute();
Schedule::command('app:auto-cancel-order-split')->everyMinute();
