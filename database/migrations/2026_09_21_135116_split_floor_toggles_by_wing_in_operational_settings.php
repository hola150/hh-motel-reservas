<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reemplaza el interruptor único "Ala Sur" (todo o nada) por control
     * fino piso+ala -- el motel prioriza el Ala Norte y va abriendo el Ala
     * Sur piso por piso según la capacidad que necesite. El piso 1 (GO
     * 101-103) no tiene ala física, así que mantiene un solo interruptor.
     */
    public function up(): void
    {
        Schema::table('operational_settings', function (Blueprint $table) {
            $table->boolean('piso_2_norte_enabled')->default(true)->after('piso_2_enabled');
            $table->boolean('piso_2_sur_enabled')->default(true)->after('piso_2_norte_enabled');
            $table->boolean('piso_3_norte_enabled')->default(true)->after('piso_3_enabled');
            $table->boolean('piso_3_sur_enabled')->default(true)->after('piso_3_norte_enabled');
        });

        $setting = DB::table('operational_settings')->where('id', 1)->first();
        if ($setting) {
            DB::table('operational_settings')->where('id', 1)->update([
                'piso_2_norte_enabled' => (bool) $setting->piso_2_enabled,
                'piso_2_sur_enabled' => (bool) $setting->piso_2_enabled && (bool) $setting->ala_sur_enabled,
                'piso_3_norte_enabled' => (bool) $setting->piso_3_enabled,
                'piso_3_sur_enabled' => (bool) $setting->piso_3_enabled && (bool) $setting->ala_sur_enabled,
            ]);
        }

        Schema::table('operational_settings', function (Blueprint $table) {
            $table->dropColumn(['ala_sur_enabled', 'piso_2_enabled', 'piso_3_enabled']);
        });
    }

    public function down(): void
    {
        Schema::table('operational_settings', function (Blueprint $table) {
            $table->boolean('ala_sur_enabled')->default(true);
            $table->boolean('piso_2_enabled')->default(true);
            $table->boolean('piso_3_enabled')->default(true);
        });

        $setting = DB::table('operational_settings')->where('id', 1)->first();
        if ($setting) {
            DB::table('operational_settings')->where('id', 1)->update([
                'ala_sur_enabled' => (bool) $setting->piso_2_sur_enabled || (bool) $setting->piso_3_sur_enabled,
                'piso_2_enabled' => (bool) $setting->piso_2_norte_enabled || (bool) $setting->piso_2_sur_enabled,
                'piso_3_enabled' => (bool) $setting->piso_3_norte_enabled || (bool) $setting->piso_3_sur_enabled,
            ]);
        }

        Schema::table('operational_settings', function (Blueprint $table) {
            $table->dropColumn(['piso_2_norte_enabled', 'piso_2_sur_enabled', 'piso_3_norte_enabled', 'piso_3_sur_enabled']);
        });
    }
};
