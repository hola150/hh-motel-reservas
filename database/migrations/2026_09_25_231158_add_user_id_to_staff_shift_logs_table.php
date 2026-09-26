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
        // Mismo registro real de entrada/salida que ya usan las mucamas
        // (staff_id), ahora también para cuentas de acceso al sistema
        // (anfitrión/recepción/administrador) -- una fila usa uno u otro,
        // nunca los dos. staff_id pasa a nullable para permitirlo.
        Schema::table('staff_shift_logs', function (Blueprint $table) {
            $table->foreignId('staff_id')->nullable()->change();
            $table->foreignId('user_id')->nullable()->after('staff_id')->constrained()->cascadeOnDelete();
            $table->index(['user_id', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_shift_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->foreignId('staff_id')->nullable(false)->change();
        });
    }
};
