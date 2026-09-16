<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_addons', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('booking_id')->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('booking_addons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn('quantity');
        });
    }
};
