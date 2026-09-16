<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ala física del motel según el número de habitación (últimos 3
     * dígitos del nombre, ej. "PLUS 101" -> 101):
     *  - 101 a 103: sin ala -- siempre activas, no se apagan con el filtro.
     *  - 201-206 y 301-306: Ala Norte -- prioridad operativa, siempre abierta.
     *  - El resto (207+, 307+): Ala Sur -- se puede apagar desde el tablero
     *    cuando no hace falta abrir todo el motel.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('wing')->nullable()->after('name');
        });

        DB::table('rooms')->get(['id', 'name'])->each(function ($room) {
            if (! preg_match('/(\d{3})$/', $room->name, $m)) {
                return;
            }
            $number = (int) $m[1];
            $wing = match (true) {
                $number >= 101 && $number <= 103 => null,
                ($number >= 201 && $number <= 206) || ($number >= 301 && $number <= 306) => 'norte',
                default => 'sur',
            };
            if ($wing !== null) {
                DB::table('rooms')->where('id', $room->id)->update(['wing' => $wing]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('wing');
        });
    }
};
