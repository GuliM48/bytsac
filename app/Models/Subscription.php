<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'client_id',
        'plan_id',
        'user_id',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'renovacion_automatica',
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'renovacion_automatica' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeProximasAVencer($query, int $dias = 7)
    {
        return $query->where('estado', 'activo')
            ->whereBetween('fecha_fin', [now(), now()->addDays($dias)]);
    }

    public function scopeVencidas($query)
    {
        return $query->whereIn('estado', ['activo', 'por_vencer'])
            ->where('fecha_fin', '<', now()->subDay());
    }
}
