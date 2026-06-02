<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('suscripciones:alertas-vencimiento')
    ->dailyAt('08:00')
    ->appendOutputTo(storage_path('logs/scheduler.log'));
