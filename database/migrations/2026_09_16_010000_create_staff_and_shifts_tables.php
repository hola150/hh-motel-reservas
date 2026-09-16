<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            // Texto libre en vez de enum -- así se pueden sumar roles nuevos
            // (ej. "Seguridad") sin migración, con solo crear un miembro de
            // ese rol.
            $table->string('role', 50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            // Un turno puede cruzar medianoche (ej. 22:30 a 08:30) -- ahí
            // end_time queda "menor" que start_time y se interpreta como el
            // día siguiente, en vez de guardar dos filas separadas.
            $table->time('end_time');
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['staff_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('staff');
    }
};
