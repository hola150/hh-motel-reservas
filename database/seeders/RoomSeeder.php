<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder
{
    /**
     * Habitaciones de ejemplo — números provisorios. Reemplazar por el
     * inventario real de HH Motel (número, categoría y fotos) antes de
     * usar el sistema en producción.
     */
    public function run(): void
    {
        $now = now();
        $categoryIds = DB::table('room_categories')->pluck('id', 'name');

        $rooms = [
            ['name' => 'GO 101', 'category' => 'GO'],
            ['name' => 'GO 102', 'category' => 'GO'],
            ['name' => 'LITE 108', 'category' => 'LITE'],
            ['name' => 'PLUS 205', 'category' => 'PLUS'],
            ['name' => 'PLUS 206', 'category' => 'PLUS'],
            ['name' => 'MAX 304', 'category' => 'MAX'],
        ];

        foreach ($rooms as $room) {
            DB::table('rooms')->insert([
                'room_category_id' => $categoryIds[$room['category']],
                'name' => $room['name'],
                'photos' => json_encode([]),
                'buffer_minutes' => 15,
                'operational_status' => 'activa',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }
}
