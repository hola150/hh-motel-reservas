<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            // Hasheado (bcrypt) -- login liviano por PIN para el panel/QR de
            // mucamas (ver MucamaAuthController), no es una cuenta real del
            // sistema (Staff no es un guard de auth de Laravel).
            $table->string('pin')->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('pin');
        });
    }
};
