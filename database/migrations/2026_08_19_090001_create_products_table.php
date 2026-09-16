<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catálogo de consumo/servicios que recepción puede ofrecer u ofrecer al
     * cerrar una reserva (bebidas, cervezas, juegos eróticos, higiene, etc.)
     * — antes era texto libre, ahora es una lista mantenible desde admin.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable(); // Bebidas, Cervezas, Juegos, Higiene, Otro
            $table->unsignedInteger('price');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
