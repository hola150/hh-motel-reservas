<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La respuesta (con IA o manual) que se le dio a la reseña en GHL --
     * llega en un segundo POST al mismo webhook cuando la acción "AI
     * Reply"/responder corre después del trigger en el Workflow.
     */
    public function up(): void
    {
        Schema::table('external_reviews', function (Blueprint $table) {
            $table->text('reply')->nullable()->after('comment');
        });
    }

    public function down(): void
    {
        Schema::table('external_reviews', function (Blueprint $table) {
            $table->dropColumn('reply');
        });
    }
};
