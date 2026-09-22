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
        Schema::table('staff', function (Blueprint $table) {
            // Se actualiza cada vez que una mucama logueada abre el QR de
            // una habitación (no solo al reportar aseo listo) -- así
            // recepción sabe dónde anda cada una ahora mismo, no solo
            // cuándo terminó algo.
            $table->foreignId('last_qr_room_id')->nullable()->after('pin')->constrained('rooms')->nullOnDelete();
            $table->timestampTz('last_qr_seen_at')->nullable()->after('last_qr_room_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropConstrainedForeignId('last_qr_room_id');
            $table->dropColumn('last_qr_seen_at');
        });
    }
};
