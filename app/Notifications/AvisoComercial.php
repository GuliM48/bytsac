<?php

namespace App\Notifications;

use App\Mail\AvisoComercialMailable;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AvisoComercial extends Notification
{
    use Queueable;

    public Subscription $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): AvisoComercialMailable
    {
        return (new AvisoComercialMailable($this->subscription))
            ->to($notifiable->email);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'client_id' => $this->subscription->client_id,
            'plan_id' => $this->subscription->plan_id,
            'cliente' => $this->subscription->client->razon_social,
            'plan' => $this->subscription->plan->nombre,
            'fecha_fin' => $this->subscription->fecha_fin->format('Y-m-d'),
            'tipo' => 'aviso_comercial',
        ];
    }
}
