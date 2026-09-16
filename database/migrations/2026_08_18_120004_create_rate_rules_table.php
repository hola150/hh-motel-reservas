<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "HH", "HOT"
            $table->text('description')->nullable();
            $table->unsignedInteger('extra_person_price')->default(0); // valor por persona adicional, independiente de categoría
            $table->unsignedSmallInteger('priority')->default(0); // desempate cuando dos reglas aplican al mismo horario
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_rules');
    }
};
