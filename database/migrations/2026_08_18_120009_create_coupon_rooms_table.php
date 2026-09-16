<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Si un cupón no tiene filas aquí ni en coupon_room_categories, aplica a todas las habitaciones.
     */
    public function up(): void
    {
        Schema::create('coupon_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->unique(['coupon_id', 'room_id']);
        });

        Schema::create('coupon_room_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_category_id')->constrained()->cascadeOnDelete();
            $table->unique(['coupon_id', 'room_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_room_categories');
        Schema::dropIfExists('coupon_rooms');
    }
};
