<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // This scope is registered from each model's booted() via BelongsToTenant trait.
        if (auth()->check()) {
            $builder->where('tenant_id', auth()->user()?->tenant_id ?? 0);
        }
    }
}
