<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Links a fotos/videos alojados afuera (GHL u otro) -- no se sube
     * ningun archivo al servidor, solo se guardan las URLs.
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->jsonb('videos')->nullable()->after('photos');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('videos');
        });
    }
};
