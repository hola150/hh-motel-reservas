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
        Schema::table('rooms', function (Blueprint $table) {
            // La mucama escanea el QR de la puerta y reporta "aseo listo" sin
            // login (ver RoomQrController) -- la habitación SIGUE en 'aseo'
            // hasta que recepción confirme desde el tablero; esto solo dice
            // que alguien ya lo reportó y con quién.
            $table->string('aseo_reported_by')->nullable()->after('aseo_started_at');
            $table->timestampTz('aseo_reported_at')->nullable()->after('aseo_reported_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['aseo_reported_by', 'aseo_reported_at']);
        });
    }
};
