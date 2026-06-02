<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('estado')->default('activo');
            $table->boolean('renovacion_automatica')->default(false);
            $table->unsignedBigInteger('tenant_id');
            $table->timestamps();

            $table->index('estado');
            $table->index('fecha_fin');
            $table->index(['estado', 'fecha_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
