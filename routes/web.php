<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;

Route::get('/run-schedule', function () {
    $token = Request::get('token');

    if ($token !== env('SCHEDULE_TOKEN')) {
        abort(403, 'Unauthorized');
    }

    Artisan::call('schedule:run');
    return '✅ Scheduler triggered at ' . now();
});
