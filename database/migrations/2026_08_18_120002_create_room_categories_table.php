<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // GO, LITE, PLUS, MAX
            $table->string('former_name')->nullable(); // ex-S, ex Estándar, ex XL...
            $table->text('description')->nullable();
            $table->jsonb('features')->nullable(); // ["Baño exterior privado", "LED"...]
            $table->unsignedSmallInteger('base_capacity')->default(2);
            $table->unsignedInteger('extra_guest_from')->default(3); // desde qué N° de persona cobra adicional
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_categories');
    }
};
