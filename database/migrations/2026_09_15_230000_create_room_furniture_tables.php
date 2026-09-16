<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('furniture_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });
        Schema::create('furniture_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('furniture_category_id')->constrained()->restrictOnDelete();
            $table->string('name', 100);
            $table->timestamps();
            $table->unique(['furniture_category_id', 'name']);
        });
        Schema::create('room_furniture', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('furniture_item_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('quantity');
            $table->enum('condition', ['operativo', 'reparacion', 'fuera_de_uso'])->default('operativo');
            $table->string('notes', 255)->nullable();
            $table->timestamps();
            $table->unique(['room_id', 'furniture_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_furniture');
        Schema::dropIfExists('furniture_items');
        Schema::dropIfExists('furniture_categories');
    }
};
