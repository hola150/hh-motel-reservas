<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite a recepción cortar manualmente el buffer de aseo cuando terminan
     * de limpiar antes de que se cumplan los minutos configurados — sin esto,
     * la habitación queda "EN ASEO" hasta que pasa el tiempo entero sí o sí.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->timestampTz('aseo_override_at')->nullable()->after('buffer_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('aseo_override_at');
        });
    }
};
