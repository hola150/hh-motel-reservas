<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Una habitación que salió a aseo (check-out o envío manual) no puede volver
 * a "activa" directo -- tiene que pasar por "aseo listo" (con quién hizo el
 * aseo, para el registro de auditoría). RoomBoardController::updateStatus()
 * ya bloqueaba el atajo, pero no había ningún test que lo dejara fijo.
 */
class RoomAseoReactivationTest extends TestCase
{
    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_18_120001_add_role_to_users_table.php',
            '2026_08_18_120002_create_room_categories_table.php',
            '2026_08_18_120003_create_rooms_table.php',
            '2026_08_18_120018_create_audit_logs_table.php',
            '2026_08_26_120001_add_aseo_override_to_rooms_table.php',
            '2026_08_26_140001_add_aseo_started_to_rooms_table.php',
            '2026_09_22_004138_add_aseo_report_to_rooms_table.php',
            '2026_09_16_010000_create_staff_and_shifts_tables.php',
            '2026_09_16_020000_add_legal_hours_to_staff.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }

        $category = RoomCategory::create(['name' => 'GO', 'display_order' => 1]);
        $this->room = Room::create(['room_category_id' => $category->id, 'name' => 'GO 101', 'operational_status' => 'aseo']);
    }

    public function test_room_in_aseo_cannot_be_reactivated_directly(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'administrador']));

        $response = $this->post("/habitaciones/{$this->room->id}/estado", [
            'operational_status' => 'activa',
        ]);

        $response->assertSessionHasErrors('room');
        $this->assertSame('aseo', $this->room->fresh()->operational_status);
    }

    public function test_marking_aseo_ready_reactivates_the_room(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'administrador']));
        Staff::create(['name' => 'Ana Mucama', 'role' => 'Mucama', 'is_active' => true]);

        $response = $this->post("/habitaciones/{$this->room->id}/aseo-listo", [
            'cleaned_by' => 'Ana Mucama',
        ]);

        $response->assertRedirect();
        $this->assertSame('activa', $this->room->fresh()->operational_status);
    }

    public function test_marking_aseo_ready_rejects_a_name_outside_the_active_roster(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'administrador']));

        $response = $this->post("/habitaciones/{$this->room->id}/aseo-listo", [
            'cleaned_by' => 'Nadie Registrado',
        ]);

        $response->assertSessionHasErrors('cleaned_by');
        $this->assertSame('aseo', $this->room->fresh()->operational_status);
    }

    /**
     * El QR de la puerta lo escanea la mucama SIN login -- confirma que la
     * ruta pública realmente no exige auth, que deja la habitación en
     * 'aseo' (no la reactiva sola) y que recepción ve ese reporte como
     * contexto al confirmar.
     */
    public function test_maid_can_report_aseo_via_the_public_qr_route_without_login(): void
    {
        Staff::create(['name' => 'Ana Mucama', 'role' => 'Mucama', 'is_active' => true]);

        $response = $this->post("/qr/habitacion/{$this->room->id}/aseo", [
            'cleaned_by' => 'Ana Mucama',
        ]);

        $response->assertRedirect();
        $fresh = $this->room->fresh();
        $this->assertSame('aseo', $fresh->operational_status);
        $this->assertSame('Ana Mucama', $fresh->aseo_reported_by);
        $this->assertNotNull($fresh->aseo_reported_at);

        // Recepción confirma después -- el reporte de la mucama se limpia,
        // no queda arrastrado para el próximo ciclo de aseo.
        $this->actingAs(User::factory()->create(['role' => 'administrador']));
        $this->post("/habitaciones/{$this->room->id}/aseo-listo", ['cleaned_by' => 'Ana Mucama']);

        $confirmed = $this->room->fresh();
        $this->assertSame('activa', $confirmed->operational_status);
        $this->assertNull($confirmed->aseo_reported_by);
        $this->assertNull($confirmed->aseo_reported_at);
    }

    public function test_reporting_aseo_is_rejected_once_the_room_is_no_longer_awaiting_it(): void
    {
        Staff::create(['name' => 'Ana Mucama', 'role' => 'Mucama', 'is_active' => true]);
        $this->room->update(['operational_status' => 'activa']);

        $response = $this->post("/qr/habitacion/{$this->room->id}/aseo", [
            'cleaned_by' => 'Ana Mucama',
        ]);

        $response->assertSessionHasErrors('cleaned_by');
        $this->assertNull($this->room->fresh()->aseo_reported_at);
    }
}
