<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // 'rut' ya existe (documento chileno). Para pasaporte se usa un
            // campo aparte porque el formato/validación no tiene nada que
            // ver con el dígito verificador del RUT.
            $table->string('document_type', 12)->nullable()->after('rut');
            $table->string('passport_number', 30)->nullable()->after('document_type');
            $table->string('nationality', 60)->nullable()->after('passport_number');
            $table->date('birth_date')->nullable()->after('nationality');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'passport_number', 'nationality', 'birth_date']);
        });
    }
};
