<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_rule_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_category_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedInteger('price'); // CLP, sin decimales
            $table->timestamps();

            $table->unique(['rate_rule_id', 'room_category_id', 'duration_minutes'], 'rate_rule_price_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_rule_prices');
    }
};
