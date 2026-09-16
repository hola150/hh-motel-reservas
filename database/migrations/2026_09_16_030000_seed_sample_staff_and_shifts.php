<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Personal y turnos de ejemplo (semana del 14 al 20 de septiembre de
     * 2026) para que el panel de Turnos no se vea vacío en un ambiente
     * recién desplegado -- mismos datos que se cargaron en local para
     * probar la funcionalidad.
     */
    public function up(): void
    {
        $staff = [
            ['name' => 'Leonardo', 'role' => 'Anfitrión', 'legal_hours_per_week' => 45],
            ['name' => 'Pamela', 'role' => 'Anfitrión', 'legal_hours_per_week' => 45],
            ['name' => 'Nevenka', 'role' => 'Anfitrión', 'legal_hours_per_week' => 45],
            ['name' => 'Javier', 'role' => 'Anfitrión', 'legal_hours_per_week' => 45],
            ['name' => 'Jorge', 'role' => 'Anfitrión', 'legal_hours_per_week' => 45],
            ['name' => 'Felipe P.', 'role' => 'Anfitrión', 'legal_hours_per_week' => 30],
            ['name' => 'Leanne', 'role' => 'Anfitrión', 'legal_hours_per_week' => 30],
            ['name' => 'Dubraska', 'role' => 'Mucama', 'legal_hours_per_week' => 45],
            ['name' => 'Fathy', 'role' => 'Mucama', 'legal_hours_per_week' => 30],
            ['name' => 'Carol', 'role' => 'Mucama', 'legal_hours_per_week' => 30],
            ['name' => 'Angel', 'role' => 'Mucama', 'legal_hours_per_week' => 30],
        ];

        $staffIds = [];
        foreach ($staff as $person) {
            $id = DB::table('staff')->where('name', $person['name'])->where('role', $person['role'])->value('id');
            if (! $id) {
                $id = DB::table('staff')->insertGetId([
                    'name' => $person['name'],
                    'role' => $person['role'],
                    'legal_hours_per_week' => $person['legal_hours_per_week'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $staffIds[$person['name']] = $id;
        }

        $shifts = [
            ['Dubraska', '2026-09-14', '09:00:00', '18:00:00'],
            ['Leonardo', '2026-09-14', '10:30:00', '19:30:00'],
            ['Nevenka', '2026-09-14', '17:30:00', '03:00:00'],
            ['Dubraska', '2026-09-15', '09:00:00', '18:00:00'],
            ['Leonardo', '2026-09-15', '10:30:00', '19:30:00'],
            ['Nevenka', '2026-09-15', '17:30:00', '03:00:00'],
            ['Dubraska', '2026-09-16', '09:00:00', '18:00:00'],
            ['Pamela', '2026-09-16', '10:30:00', '19:30:00'],
            ['Dubraska', '2026-09-17', '09:00:00', '18:00:00'],
            ['Pamela', '2026-09-17', '14:15:00', '22:15:00'],
            ['Nevenka', '2026-09-17', '20:30:00', '03:00:00'],
            ['Fathy', '2026-09-18', '08:00:00', '13:00:00'],
            ['Felipe P.', '2026-09-18', '09:30:00', '17:00:00'],
            ['Dubraska', '2026-09-18', '14:00:00', '21:30:00'],
            ['Leonardo', '2026-09-18', '14:15:00', '22:15:00'],
            ['Pamela', '2026-09-18', '17:00:00', '22:00:00'],
            ['Nevenka', '2026-09-18', '22:30:00', '03:00:00'],
            ['Carol', '2026-09-19', '07:00:00', '16:00:00'],
            ['Leanne', '2026-09-19', '08:00:00', '16:30:00'],
            ['Felipe P.', '2026-09-19', '09:30:00', '17:00:00'],
            ['Jorge', '2026-09-19', '13:45:00', '22:30:00'],
            ['Angel', '2026-09-19', '14:00:00', '21:00:00'],
            ['Nevenka', '2026-09-19', '22:30:00', '03:00:00'],
            ['Carol', '2026-09-20', '07:00:00', '16:00:00'],
            ['Javier', '2026-09-20', '07:30:00', '14:30:00'],
            ['Leonardo', '2026-09-20', '14:15:00', '22:15:00'],
        ];

        foreach ($shifts as [$name, $date, $start, $end]) {
            $staffId = $staffIds[$name];
            $exists = DB::table('shifts')->where('staff_id', $staffId)->where('date', $date)->where('start_time', $start)->exists();
            if (! $exists) {
                DB::table('shifts')->insert([
                    'staff_id' => $staffId,
                    'date' => $date,
                    'start_time' => $start,
                    'end_time' => $end,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $names = ['Leonardo', 'Pamela', 'Nevenka', 'Javier', 'Jorge', 'Felipe P.', 'Leanne', 'Dubraska', 'Fathy', 'Carol', 'Angel'];
        $staffIds = DB::table('staff')->whereIn('name', $names)->pluck('id');
        DB::table('shifts')->whereIn('staff_id', $staffIds)->delete();
        DB::table('staff')->whereIn('id', $staffIds)->delete();
    }
};
