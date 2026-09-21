<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La frase de venta corta del catálogo ("Ambiente íntimo con...")
     * estaba fija en CatalogController -- ahora es editable por categoría
     * desde /admin/categorias. Se rellena acá con los textos que ya estaban
     * hardcodeados, para no perder lo que ya había.
     */
    public function up(): void
    {
        Schema::table('room_categories', function (Blueprint $table) {
            $table->string('sales_tip', 300)->nullable()->after('description');
        });

        $tips = [
            'GO' => 'Ambiente íntimo con mobiliario seleccionado para disfrutar una experiencia diferente.',
            'LITE' => 'Privacidad y comodidad en un ambiente equipado para compartir sin apuros.',
            'NEW LITE' => 'Baño interior con ducha integrada al ambiente, visible desde la cama.',
            'PLUS' => 'Un espacio preparado para disfrutar su mobiliario y vivir una experiencia más intensa.',
            'MAX' => 'Ambiente amplio para explorar y disfrutar en compañía; una de las preferidas para grupos de más de dos personas.',
        ];

        foreach ($tips as $name => $tip) {
            DB::table('room_categories')->where('name', $name)->update(['sales_tip' => $tip]);
        }
    }

    public function down(): void
    {
        Schema::table('room_categories', function (Blueprint $table) {
            $table->dropColumn('sales_tip');
        });
    }
};
