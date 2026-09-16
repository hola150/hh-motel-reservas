<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ventanas semanales de una tarifa. HH y HOT se descomponen en filas por
     * día de la semana para poder representar tanto un horario que se reinicia
     * cada noche (HH) como un bloque continuo de varios días (HOT: viernes
     * "envuelve" al sábado, sábado es día completo, domingo termina a las 22:30).
     */
    public function up(): void
    {
        Schema::create('rate_rule_windows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_rule_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // 0 = domingo ... 6 = sábado (ISO-ish, domingo=0)
            $table->time('start_time');
            $table->time('end_time');
            // Si end_time es menor que start_time, la ventana continúa después de medianoche
            // hacia el weekday siguiente (ej. HH: 10:30 -> 03:00).
            $table->boolean('wraps_midnight')->default(false);
            $table->timestamps();

            $table->index(['rate_rule_id', 'weekday']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_rule_windows');
    }
};
