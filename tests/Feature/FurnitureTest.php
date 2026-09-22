<?php

namespace Tests\Feature;

use App\Models\FurnitureCategory;
use App\Models\FurnitureItem;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FurnitureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Only the module dependencies: the full booking schema is PostgreSQL-specific.
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2026_08_18_120001_add_role_to_users_table.php',
            '2026_08_18_120002_create_room_categories_table.php',
            '2026_08_18_120003_create_rooms_table.php',
            '2026_08_18_120018_create_audit_logs_table.php',
            '2026_09_15_230000_create_room_furniture_tables.php',
            '2026_09_16_050000_add_videos_to_rooms.php',
        ] as $migration) {
            (require database_path('migrations/'.$migration))->up();
        }
        $this->actingAs(User::factory()->create(['role' => 'administrador']));
    }

    public function test_catalog_and_room_assignment_are_persisted_and_audited(): void
    {
        $this->post('/admin/mobiliario/categorias', ['name' => 'Mobiliario erótico'])->assertRedirect();
        $category = FurnitureCategory::firstOrFail();
        $this->post('/admin/mobiliario/elementos', ['name' => 'Sillón', 'furniture_category_id' => $category->id])->assertRedirect();
        $item = FurnitureItem::firstOrFail();
        $roomCategory = RoomCategory::create(['name' => 'Prueba', 'display_order' => 1]);
        $data = [
            'name' => 'Prueba 01', 'room_category_id' => $roomCategory->id,
            'buffer_minutes' => 15, 'operational_status' => 'activa', 'furniture_present' => '1',
            'furniture' => [$item->id => ['id' => $item->id, 'quantity' => 2, 'condition' => 'reparacion', 'notes' => 'Revisar soporte']],
        ];
        $this->post('/admin/habitaciones', $data)->assertSessionHasNoErrors()->assertRedirect('/admin/habitaciones');
        $room = Room::firstOrFail();
        $this->assertDatabaseHas('room_furniture', ['room_id' => $room->id, 'quantity' => 2, 'condition' => 'reparacion']);
        $this->get('/admin/habitaciones/'.$room->id.'/editar')->assertOk()->assertSee('Sillón')->assertSee('Revisar soporte');
        $this->get('/admin/mobiliario')->assertOk()->assertSee('Mobiliario erótico');
        $data['furniture'][$item->id]['quantity'] = 0;
        $this->put('/admin/habitaciones/'.$room->id, $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('room_furniture', 0);
        $this->assertDatabaseHas('audit_logs', ['action' => 'habitacion.editar', 'entity_id' => (string) $room->id]);
    }

    public function test_invalid_assignment_does_not_create_a_room(): void
    {
        $category = RoomCategory::create(['name' => 'Prueba', 'display_order' => 1]);
        $this->post('/admin/habitaciones', [
            'name' => 'Prueba inválida', 'room_category_id' => $category->id,
            'buffer_minutes' => 15, 'operational_status' => 'activa', 'furniture_present' => '1',
            'furniture' => [['id' => 999, 'quantity' => -1, 'condition' => 'inexistente']],
        ])->assertSessionHasErrors(['furniture.0.id', 'furniture.0.quantity', 'furniture.0.condition']);
        $this->assertDatabaseCount('rooms', 0);
    }
}
