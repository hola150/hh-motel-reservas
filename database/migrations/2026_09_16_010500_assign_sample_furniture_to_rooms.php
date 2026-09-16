<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Asigna al azar algunos elementos del catálogo de mobiliario a un puñado
     * de habitaciones (una por categoría aprox.), para poder ver cómo se ven
     * los íconos ya publicados en el tablero real, no solo en el catálogo.
     */
    public function up(): void
    {
        $assignments = [
            ['GO 201', 'Sling tela'], ['GO 201', 'Tántrico'],
            ['GO 301', 'Guillotina'], ['GO 301', 'X'], ['GO 301', 'Tijeras'],
            ['LITE 208', 'Guillotina'], ['LITE 208', 'Caballete matroneitor'],
            ['LITE 309', 'Sling tela'], ['LITE 309', 'Caballete madera'], ['LITE 309', 'Caballete matroneitor'],
            ['MAX 103', 'Circular'], ['MAX 103', 'Tántrico'],
            ['MAX 210', 'Sling tela'], ['MAX 210', 'Rectangular'],
            ['NEW LITE 205', 'Sling tela'], ['NEW LITE 205', 'Caballete matroneitor'], ['NEW LITE 205', 'Caballete madera'],
            ['NEW LITE 304', 'Sling tela'], ['NEW LITE 304', 'X'],
            ['PLUS 101', 'Tántrico'], ['PLUS 101', 'Guillotina'], ['PLUS 101', 'Caballete matroneitor'],
            ['PLUS 306', 'Caballete madera'], ['PLUS 306', 'Caballete matroneitor'], ['PLUS 306', 'X'],
        ];

        foreach ($assignments as [$roomName, $itemName]) {
            $roomId = DB::table('rooms')->where('name', $roomName)->value('id');
            $itemId = DB::table('furniture_items')->where('name', $itemName)->value('id');
            if (! $roomId || ! $itemId) {
                continue;
            }
            $exists = DB::table('room_furniture')->where('room_id', $roomId)->where('furniture_item_id', $itemId)->exists();
            if (! $exists) {
                DB::table('room_furniture')->insert([
                    'room_id' => $roomId,
                    'furniture_item_id' => $itemId,
                    'quantity' => 1,
                    'condition' => 'operativo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $roomNames = ['GO 201', 'GO 301', 'LITE 208', 'LITE 309', 'MAX 103', 'MAX 210', 'NEW LITE 205', 'NEW LITE 304', 'PLUS 101', 'PLUS 306'];
        $roomIds = DB::table('rooms')->whereIn('name', $roomNames)->pluck('id');
        DB::table('room_furniture')->whereIn('room_id', $roomIds)->delete();
    }
};
