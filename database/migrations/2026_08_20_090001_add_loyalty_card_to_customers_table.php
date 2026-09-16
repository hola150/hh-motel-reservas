<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Se trackea localmente (no depende de la integración en vivo con GHL,
     * que todavía no está conectada) para poder avisarle a recepción de
     * inmediato cuando un cliente todavía no tiene la tarjeta.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('has_loyalty_card')->default(false)->after('rut');
            $table->string('loyalty_card_number')->nullable()->after('has_loyalty_card');
            $table->timestampTz('loyalty_card_added_at')->nullable()->after('loyalty_card_number');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['has_loyalty_card', 'loyalty_card_number', 'loyalty_card_added_at']);
        });
    }
};
