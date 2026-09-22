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
        // Registro REAL de entrada/salida (cuándo entró de verdad, no lo
        // planificado) -- distinto de "shifts" (el horario programado). Se
        // abre solo al loguearse con PIN si no tenía uno ya abierto, y se
        // cierra al cerrar sesión -- ver MucamaAuthController.
        Schema::create('staff_shift_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->timestampTz('started_at');
            $table->timestampTz('ended_at')->nullable();
            $table->timestamps();

            $table->index(['staff_id', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_shift_logs');
    }
};
