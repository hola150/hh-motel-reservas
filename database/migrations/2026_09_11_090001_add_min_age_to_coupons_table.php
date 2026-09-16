<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            // Antes "requiere verificación" era solo una nota para que
            // recepción mirara la cédula a ojo — ahora, si el cupón trae
            // min_age, el sistema exige que el cliente tenga fecha de
            // nacimiento cargada y que la edad calculada la cumpla.
            $table->unsignedTinyInteger('min_age')->nullable()->after('requires_verification');
        });

        DB::table('coupons')->where('code', 'EXPERTOS EN VIDA')->update(['min_age' => 65]);
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('min_age');
        });
    }
};
