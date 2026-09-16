<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite a recepción mandar una habitación a aseo a mano —por ejemplo la
     * revisó y estaba sucia, aunque el sistema no tenga un check-out reciente
     * que la explique— sin depender de que exista una reserva de por medio.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->timestampTz('aseo_started_at')->nullable()->after('aseo_override_at');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('aseo_started_at');
        });
    }
};
