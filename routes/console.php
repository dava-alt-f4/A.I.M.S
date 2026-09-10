<?php

use App\Models\User;
use App\Services\FinanceService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (FinanceService $financeService) {
    $users = User::all();

    foreach ($users as $user) {
        $financeService->processAutoSweep($user);
    }
})->lastDayOfMonth('23:59')->name('harvey:auto-sweep');
