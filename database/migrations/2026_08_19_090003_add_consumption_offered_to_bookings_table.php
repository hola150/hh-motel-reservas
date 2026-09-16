<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Deja constancia de que recepción efectivamente ofreció el listado de
     * consumo antes de cerrar la reserva — es lo que hace obligatorio el paso,
     * no solo una sugerencia en pantalla.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestampTz('consumption_offered_at')->nullable()->after('notes');
            $table->foreignId('consumption_offered_by')->nullable()->after('consumption_offered_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('consumption_offered_by');
            $table->dropColumn('consumption_offered_at');
        });
    }
};
