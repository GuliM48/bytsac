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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->decimal('precio_mensual', 10, 2);
            $table->decimal('precio_anual', 10, 2);
            $table->boolean('control_ventas_stock')->default(false);
            $table->integer('max_usuarios')->default(1);
            $table->enum('nivel_reportes', ['basico', 'avanzado', 'premium'])->default('basico');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
