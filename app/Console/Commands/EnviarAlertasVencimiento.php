<?php

namespace App\Console\Commands;

use App\Jobs\EnviarAlertasVencimiento as EnviarAlertasVencimientoJob;
use Illuminate\Console\Command;

class EnviarAlertasVencimiento extends Command
{
    protected $signature = 'suscripciones:alertas-vencimiento';

    protected $description = 'Revisa suscripciones por vencer y envía notificaciones';

    public function handle(): void
    {
        EnviarAlertasVencimientoJob::dispatch();

        $this->info('Alertas de vencimiento encoladas correctamente.');
    }
}
