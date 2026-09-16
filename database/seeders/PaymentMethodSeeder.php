<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('payment_methods')->insert([
            ['code' => 'efectivo', 'name' => 'Efectivo', 'requires_external_id' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'transferencia', 'name' => 'Transferencia', 'requires_external_id' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'debito', 'name' => 'Débito', 'requires_external_id' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'credito', 'name' => 'Crédito', 'requires_external_id' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'mercado_pago', 'name' => 'Mercado Pago', 'requires_external_id' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'otro', 'name' => 'Otro', 'requires_external_id' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
