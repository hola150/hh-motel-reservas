<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            // Horas semanales que corresponden por contrato -- lo que se
            // agende por sobre esto en el panel de turnos cuenta como hora
            // extra. Nullable: sin dato cargado, no se calcula extra para
            // esa persona (no se asume un valor por defecto).
            $table->unsignedSmallInteger('legal_hours_per_week')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('legal_hours_per_week');
        });
    }
};
