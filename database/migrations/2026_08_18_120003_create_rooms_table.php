<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_category_id')->constrained()->restrictOnDelete();
            $table->string('name'); // ej. "PLUS 206"
            $table->jsonb('photos')->nullable();
            $table->unsignedSmallInteger('buffer_minutes')->default(15); // tiempo de aseo entre reservas
            $table->enum('operational_status', ['activa', 'mantencion', 'inactiva'])->default('activa');
            $table->text('operational_note')->nullable(); // motivo de mantención, etc.
            $table->timestamps();

            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
