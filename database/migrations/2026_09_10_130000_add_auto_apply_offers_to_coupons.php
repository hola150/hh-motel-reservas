<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ofertas programadas: un cupón con auto_apply = true se aplica solo, sin
     * código, cuando el cliente elige una habitación en oferta. Reusa todo el
     * motor de cupones (vigencia, días, horarios, scope por habitación o
     * categoría). Nuevo tipo de descuento 'precio_fijo': discount_value es el
     * precio final ofertado (ej. PLUS a $28.000), no el monto a restar.
     */
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->boolean('auto_apply')->default(false)->after('code');
        });

        // Las ofertas no tienen código — se permite null (el índice único deja
        // pasar varios null en Postgres). Sintaxis ALTER COLUMN/CONSTRAINT de
        // Postgres -- en SQLite (tests) code ya nace nullable y discount_type
        // ya incluye 'precio_fijo' desde create_coupons_table.
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE coupons ALTER COLUMN code DROP NOT NULL');

            DB::statement('ALTER TABLE coupons DROP CONSTRAINT IF EXISTS coupons_discount_type_check');
            DB::statement("ALTER TABLE coupons ADD CONSTRAINT coupons_discount_type_check CHECK (discount_type IN ('percentage', 'fixed', 'precio_fijo'))");
        }
    }

    public function down(): void
    {
        DB::statement("UPDATE coupons SET discount_type = 'fixed' WHERE discount_type = 'precio_fijo'");
        DB::statement('ALTER TABLE coupons DROP CONSTRAINT IF EXISTS coupons_discount_type_check');
        DB::statement("ALTER TABLE coupons ADD CONSTRAINT coupons_discount_type_check CHECK (discount_type IN ('percentage', 'fixed'))");

        DB::statement("UPDATE coupons SET code = 'SIN-CODIGO-'||id WHERE code IS NULL");
        DB::statement('ALTER TABLE coupons ALTER COLUMN code SET NOT NULL');

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('auto_apply');
        });
    }
};
