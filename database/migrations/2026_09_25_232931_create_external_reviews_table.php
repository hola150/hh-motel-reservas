<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reseñas públicas reales (Google/Facebook) sincronizadas desde GHL vía
     * su trigger de automatización "Reviews Received" -- distinto de
     * guest_reviews (nuestro propio filtro de 1-5 estrellas antes de llegar
     * a Google). raw_payload guarda el body completo tal cual llega,
     * porque el formato exacto de ese webhook no está documentado
     * públicamente -- así no se pierde nada si el parseo de algún campo
     * falla o cambia.
     */
    public function up(): void
    {
        Schema::create('external_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('source')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('comment')->nullable();
            $table->string('reviewer_name')->nullable();
            $table->string('external_id')->nullable()->unique();
            $table->timestampTz('reviewed_at')->nullable();
            $table->json('raw_payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_reviews');
    }
};
