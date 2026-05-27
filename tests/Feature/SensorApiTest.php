<?php

namespace Tests\Feature;

use App\Models\Node;
use App\Models\PumpControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_sensor_api_accepts_hardware_payload_and_returns_pump_status(): void
    {
        $node = Node::create([
            'kode_node' => 'NODE-01',
            'nama_node' => 'Node Kebun-1',
            'lokasi' => 'Ladang-1',
            'status' => 'Nonaktif',
        ]);

        PumpControl::create([
            'node_id' => $node->id,
            'status' => 'ON',
            'triggered_by' => 'web',
        ]);

        $response = $this->postJson('/api/sensor', [
            'node_code' => 'NODE-01',
            'soil_moisture' => 71.5,
            'temperature' => 28.7,
            'ph' => 6.8,
            'flow' => 12.1,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('node.kode_node', 'NODE-01');
        $response->assertJsonPath('pump_status', 'ON');
        $response->assertJsonPath('pump_on', true);

        $this->assertDatabaseHas('sensor_readings', [
            'node_id' => $node->id,
            'kelembaban_tanah' => 71.5,
            'suhu' => 28.7,
            'ph_air' => 6.8,
            'debit_air' => 12.1,
        ]);
        $this->assertDatabaseHas('nodes', [
            'id' => $node->id,
            'status' => 'Aktif',
        ]);
    }

    public function test_hardware_can_read_pump_status_by_node_code(): void
    {
        $node = Node::create([
            'kode_node' => 'NODE-01',
            'nama_node' => 'Node Kebun-1',
            'lokasi' => 'Ladang-1',
            'status' => 'Aktif',
        ]);

        PumpControl::create([
            'node_id' => $node->id,
            'status' => 'OFF',
            'triggered_by' => 'web',
        ]);

        $response = $this->getJson('/api/pump/NODE-01');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('kode_node', 'NODE-01');
        $response->assertJsonPath('pump_status', 'OFF');
        $response->assertJsonPath('pump_on', false);
    }

    public function test_wemos_can_send_sensor_data_with_query_string_aliases(): void
    {
        $node = Node::create([
            'kode_node' => 'WEMOS-01',
            'nama_node' => 'Wemos Kebun',
            'lokasi' => 'Bedeng 1',
            'status' => 'Nonaktif',
        ]);

        $response = $this->getJson('/api/sensor?device=WEMOS-01&soil=68.2&temp_c=29.4&phAir=6.6&flowRate=10.7');

        $response->assertCreated();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('node.kode_node', 'WEMOS-01');
        $response->assertJsonPath('pump_status', 'OFF');
        $response->assertJsonPath('pump_on', false);

        $this->assertDatabaseHas('sensor_readings', [
            'node_id' => $node->id,
            'kelembaban_tanah' => 68.2,
            'suhu' => 29.4,
            'ph_air' => 6.6,
            'debit_air' => 10.7,
        ]);
    }

    public function test_sensor_api_rejects_unknown_node(): void
    {
        $response = $this->postJson('/api/sensor', [
            'kode_node' => 'NODE-404',
            'kelembaban_tanah' => 50,
        ]);

        $response->assertNotFound();
        $response->assertJsonPath('success', false);
    }

    public function test_api_health_check_is_available(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
