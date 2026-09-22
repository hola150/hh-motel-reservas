<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            // Nullable desde el arranque -- las ofertas (auto_apply, ver
            // 2026_09_10_130000_add_auto_apply_offers_to_coupons) no tienen
            // código, y en SQLite (tests) esa migración no puede aflojar un
            // NOT NULL después vía ALTER COLUMN como en Postgres.
            $table->string('code')->nullable()->unique(); // MORNING ESCAPE, EXPERTOS EN VIDA...
            $table->string('internal_name');
            // 'precio_fijo' se agregó después (ver esa misma migración) --
            // incluido acá desde el arranque por la misma razón de arriba.
            $table->enum('discount_type', ['percentage', 'fixed', 'precio_fijo']);
            $table->unsignedInteger('discount_value'); // % (0-100) o CLP según discount_type
            $table->unsignedInteger('min_amount')->nullable();
            $table->unsignedInteger('max_discount_amount')->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->jsonb('allowed_weekdays')->nullable(); // [1,2,3,4] lun-jue; null = todos
            $table->time('allowed_time_start')->nullable();
            $table->time('allowed_time_end')->nullable();
            $table->jsonb('allowed_durations')->nullable(); // minutos permitidos; null = todas
            $table->unsignedInteger('max_uses_total')->nullable();
            $table->unsignedInteger('max_uses_per_customer')->nullable();
            $table->boolean('is_stackable')->default(false);
            $table->boolean('requires_verification')->default(false);
            $table->string('verification_note')->nullable(); // "Cédula de identidad", "Credencial vigente"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
