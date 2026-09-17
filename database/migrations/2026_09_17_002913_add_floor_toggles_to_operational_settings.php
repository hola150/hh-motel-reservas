<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operational_settings', function (Blueprint $table) {
            $table->boolean('piso_1_enabled')->default(true)->after('ala_sur_enabled');
            $table->boolean('piso_2_enabled')->default(true)->after('piso_1_enabled');
            $table->boolean('piso_3_enabled')->default(true)->after('piso_2_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('operational_settings', function (Blueprint $table) {
            $table->dropColumn(['piso_1_enabled', 'piso_2_enabled', 'piso_3_enabled']);
        });
    }
};
