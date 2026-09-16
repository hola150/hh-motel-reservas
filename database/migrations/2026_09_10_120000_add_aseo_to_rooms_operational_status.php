<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Agrega 'aseo' como estado operativo válido. Al hacer el check-out la
     * habitación pasa a 'aseo' (fuera de disponibles) y solo vuelve a 'activa'
     * cuando recepción la marca a mano — no hay cronómetro, a veces la pieza
     * queda sin hacer hasta el otro día por falta de mucamas.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE rooms DROP CONSTRAINT IF EXISTS rooms_operational_status_check');
        DB::statement("ALTER TABLE rooms ADD CONSTRAINT rooms_operational_status_check CHECK (operational_status IN ('activa', 'mantencion', 'inactiva', 'aseo'))");
    }

    public function down(): void
    {
        DB::statement("UPDATE rooms SET operational_status = 'activa' WHERE operational_status = 'aseo'");
        DB::statement('ALTER TABLE rooms DROP CONSTRAINT IF EXISTS rooms_operational_status_check');
        DB::statement("ALTER TABLE rooms ADD CONSTRAINT rooms_operational_status_check CHECK (operational_status IN ('activa', 'mantencion', 'inactiva'))");
    }
};
