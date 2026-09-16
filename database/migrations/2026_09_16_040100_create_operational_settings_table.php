<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fila única (id=1) con interruptores operativos globales -- por ahora
     * solo si el Ala Sur está habilitada para ofrecerse en el tablero.
     */
    public function up(): void
    {
        Schema::create('operational_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('ala_sur_enabled')->default(true);
            $table->timestamps();
        });

        DB::table('operational_settings')->insert(['id' => 1, 'ala_sur_enabled' => true, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_settings');
    }
};
