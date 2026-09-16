<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            // 'ingreso' = entra plata a la caja (caja chica, vuelto que se
            // devuelve al fondo, etc.) — 'egreso' = sale plata (gasto menor,
            // retiro a banco). El efectivo de las reservas NO se guarda acá:
            // se calcula solo desde payments (medio de pago = efectivo), así
            // nunca se puede duplicar ni desincronizar con la venta real.
            $table->enum('type', ['ingreso', 'egreso']);
            $table->unsignedInteger('amount');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
