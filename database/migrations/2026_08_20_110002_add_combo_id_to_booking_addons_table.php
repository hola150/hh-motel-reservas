<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_addons', function (Blueprint $table) {
            $table->foreignId('combo_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('booking_addons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('combo_id');
        });
    }
};
