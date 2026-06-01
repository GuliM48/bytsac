<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('razon_social', 200);
            $table->string('ruc', 20)->nullable();
            $table->string('direccion', 300)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 150);
            $table->unsignedBigInteger('id_usuario_creador');
            $table->enum('estado', ['activo', 'inactivo', 'suspendido'])->default('activo');
            $table->timestamps();

            $table->foreign('id_usuario_creador')->references('id')->on('users')->restrictOnDelete();
            $table->index(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'ruc']);
            $table->unique(['tenant_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
