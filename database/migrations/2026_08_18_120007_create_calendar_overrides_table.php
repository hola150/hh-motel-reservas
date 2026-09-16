<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fechas puntuales (vísperas de feriado) que fuerzan una tarifa distinta
     * a la que tocaría por día de la semana normal.
     */
    public function up(): void
    {
        Schema::create('calendar_overrides', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->foreignId('forced_rate_rule_id')->constrained('rate_rules')->cascadeOnDelete();
            $table->string('reason')->nullable(); // "Víspera de feriado"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_overrides');
    }
};
