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
        Schema::table('bookings', function (Blueprint $table) {
            // Confirmación de que la habitación está en condiciones ANTES de
            // que llegue este huésped puntual -- distinto de aseo_reported_at
            // en Room (que es "ya la limpié"), esto es "la revisé de nuevo
            // justo antes de que llegue esta reserva".
            $table->timestampTz('room_checked_at')->nullable()->after('checked_out_at');
            $table->string('room_checked_by')->nullable()->after('room_checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['room_checked_at', 'room_checked_by']);
        });
    }
};
