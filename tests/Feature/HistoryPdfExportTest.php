<?php

namespace Tests\Feature;

use App\Models\Node;
use App\Models\SensorReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryPdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_export_sensor_history_as_pdf(): void
    {
        $user = User::factory()->create();
        $node = Node::create([
            'kode_node' => 'NODE-001',
            'nama_node' => 'Node Kebun',
            'lokasi' => 'Kebun Utara',
            'status' => 'Aktif',
        ]);

        SensorReading::create([
            'node_id' => $node->id,
            'kelembaban_tanah' => 72.5,
            'suhu' => 28.4,
            'ph_air' => 6.8,
            'debit_air' => 12.2,
        ]);

        $response = $this->actingAs($user)->get(route('history.export-pdf', [
            'node_id' => $node->id,
            'date' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment; filename=riwayat-sensor-', $response->headers->get('content-disposition'));
    }
}
