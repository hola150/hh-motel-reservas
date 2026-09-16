<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hora real de entrada/salida — separada de starts_at/ends_at (lo
     * programado). El tablero usa esta hora real cuando existe, para que la
     * ocupación y el aseo reflejen lo que pasó de verdad, no solo lo agendado.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestampTz('checked_in_at')->nullable()->after('ends_at');
            $table->timestampTz('checked_out_at')->nullable()->after('checked_in_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['checked_in_at', 'checked_out_at']);
        });
    }
};
