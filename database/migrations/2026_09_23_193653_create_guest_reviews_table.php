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
        Schema::create('guest_reviews', function (Blueprint $table) {
            $table->id();
            // Nullable -- el QR genérico (pegado en recepción) no viene de
            // una reserva puntual, solo el link mandado después del check-out sí.
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating');
            // Solo se pide/guarda cuando la calificación es baja (0-3) -- a
            // las de 4-5 se las manda directo a Google, sin frenarlas a
            // escribir nada acá.
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_reviews');
    }
};
