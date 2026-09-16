<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Upsells estilo McDonald's — sugerencias con precio fijo que recepción
     * ofrece al reservar / al llegar el cliente. Tres tipos:
     *  - category_upgrade: pasar a una habitación de categoría superior
     *  - time_extension:   sumar minutos a la estadía
     *  - combo:            un pack de productos (referencia a un Combo)
     */
    public function up(): void
    {
        Schema::create('upsell_offers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // category_upgrade | time_extension | combo
            $table->integer('price')->default(0); // CLP fijo del upsell (para combo se usa el precio del combo)
            $table->foreignId('from_room_category_id')->nullable()->constrained('room_categories')->nullOnDelete();
            $table->foreignId('to_room_category_id')->nullable()->constrained('room_categories')->nullOnDelete();
            $table->unsignedSmallInteger('extra_minutes')->nullable();
            $table->foreignId('combo_id')->nullable()->constrained('combos')->nullOnDelete();
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upsell_offers');
    }
};
