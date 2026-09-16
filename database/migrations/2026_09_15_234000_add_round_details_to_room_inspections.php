<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_inspections', function (Blueprint $table) {
            $table->string('shift', 30)->nullable()->after('inspected_by');
            $table->text('defects')->nullable()->after('notes');
            $table->json('photos')->nullable()->after('defects');
        });
    }

    public function down(): void
    {
        Schema::table('room_inspections', function (Blueprint $table) {
            $table->dropColumn(['shift', 'defects', 'photos']);
        });
    }
};
