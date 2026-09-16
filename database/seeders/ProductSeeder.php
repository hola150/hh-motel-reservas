<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Catálogo de partida — ajusta nombres y precios reales desde
     * /admin/productos. Son solo un punto de partida, no precios reales.
     */
    public function run(): void
    {
        $now = now();

        $products = [
            ['category' => 'Bebidas', 'name' => 'Bebida (lata)', 'price' => 2000, 'stock' => 24],
            ['category' => 'Bebidas', 'name' => 'Agua mineral', 'price' => 2000, 'stock' => 24],
            ['category' => 'Bebidas', 'name' => 'Jugo natural', 'price' => 2500, 'stock' => 12],
            ['category' => 'Cervezas', 'name' => 'Cerveza nacional', 'price' => 3000, 'stock' => 24],
            ['category' => 'Cervezas', 'name' => 'Cerveza importada', 'price' => 4000, 'stock' => 12],
            ['category' => 'Juegos', 'name' => 'Dados eróticos', 'price' => 5000, 'stock' => 10],
            ['category' => 'Juegos', 'name' => 'Kit de juegos para parejas', 'price' => 8000, 'stock' => 10],
            ['category' => 'Higiene', 'name' => 'Gel íntimo', 'price' => 3000, 'stock' => 20],
            ['category' => 'Higiene', 'name' => 'Preservativos (pack)', 'price' => 2500, 'stock' => 20],
            ['category' => 'Otro', 'name' => 'Servicio a la habitación', 'price' => 0, 'stock' => 0, 'track_inventory' => false],
        ];

        foreach ($products as $i => $product) {
            DB::table('products')->insert([
                'name' => $product['name'],
                'category' => $product['category'],
                'price' => $product['price'],
                'track_inventory' => $product['track_inventory'] ?? true,
                'stock' => $product['stock'],
                'low_stock_threshold' => 5,
                'display_order' => $i,
                'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }
}
