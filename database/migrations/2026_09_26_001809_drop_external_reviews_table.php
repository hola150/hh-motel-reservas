<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * external_reviews (sincronización propia vía webhook de GHL) queda
     * descartada -- el widget oficial de reseñas de GHL (embebido en
     * /admin/opiniones) ya muestra la puntuación y las reseñas reales sin
     * necesidad de guardar una copia propia.
     */
    public function up(): void
    {
        Schema::dropIfExists('external_reviews');
    }

    public function down(): void
    {
        Schema::create('external_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('source')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('comment')->nullable();
            $table->text('reply')->nullable();
            $table->string('reviewer_name')->nullable();
            $table->string('external_id')->nullable()->unique();
            $table->timestampTz('reviewed_at')->nullable();
            $table->json('raw_payload');
            $table->timestamps();
        });
    }
};
