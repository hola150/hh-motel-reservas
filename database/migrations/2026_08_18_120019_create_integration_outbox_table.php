<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cola de integraciones salientes (GHL, Sheets, y luego Meta CAPI). Escribir
     * aquí es parte de la misma transacción que confirma la reserva o el pago,
     * así el evento nunca se pierde aunque el worker esté caído — ver sección 08
     * del documento de arquitectura.
     */
    public function up(): void
    {
        Schema::create('integration_outbox', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['GHL', 'SHEETS', 'META_CAPI']);
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->cascadeOnDelete();
            $table->jsonb('payload');
            $table->enum('status', ['pendiente', 'procesando', 'enviado', 'fallido'])->default('pendiente');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestampTz('processed_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_outbox');
    }
};
