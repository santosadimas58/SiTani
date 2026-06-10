<?php

namespace App\Services;

use App\Models\Node;
use App\Models\PumpControl;
use App\Models\SensorReading;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class SensorReadingIngestor
{
    public function ingest(array $input): array
    {
        $payload = $this->normalize($input);

        $validated = Validator::make($payload, [
            'kode_node' => ['required_without:node_id', 'nullable', 'string'],
            'node_id' => ['required_without:kode_node', 'nullable', 'integer', 'exists:nodes,id'],
            'kelembaban_tanah' => ['nullable', 'numeric', 'between:0,100'],
            'kelembaban_tanah_1' => ['nullable', 'numeric', 'between:0,100'],
            'kelembaban_tanah_2' => ['nullable', 'numeric', 'between:0,100'],
            'suhu' => ['nullable', 'numeric', 'between:-40,100'],
            'kelembaban_udara' => ['nullable', 'numeric', 'between:0,100'],
            'ph_air' => ['nullable', 'numeric', 'between:0,14'],
            'debit_air' => ['nullable', 'numeric', 'min:0'],
        ])->validate();

        $node = $this->findNode($validated['kode_node'] ?? null, $validated['node_id'] ?? null);

        if (! $node) {
            return [
                'success' => false,
                'error' => 'Node tidak ditemukan',
            ];
        }

        $node->update(['status' => 'Aktif']);

        $soilAverage = $validated['kelembaban_tanah'] ?? $this->averageSoil(
            $validated['kelembaban_tanah_1'] ?? null,
            $validated['kelembaban_tanah_2'] ?? null
        );

        $reading = SensorReading::create([
            'node_id' => $node->id,
            'kelembaban_tanah' => $soilAverage,
            'kelembaban_tanah_1' => $validated['kelembaban_tanah_1'] ?? null,
            'kelembaban_tanah_2' => $validated['kelembaban_tanah_2'] ?? null,
            'suhu' => $validated['suhu'] ?? null,
            'kelembaban_udara' => $validated['kelembaban_udara'] ?? null,
            'ph_air' => $validated['ph_air'] ?? null,
            'debit_air' => $validated['debit_air'] ?? null,
        ]);

        $pump = PumpControl::where('node_id', $node->id)->latest()->first();
        $pumpStatus = $pump ? $pump->status : 'OFF';

        return [
            'success' => true,
            'node' => $node,
            'reading' => $reading,
            'pump_status' => $pumpStatus,
            'pump_on' => $pumpStatus === 'ON',
        ];
    }

    public function normalize(array $input): array
    {
        return [
            'kode_node' => Arr::get($input, 'kode_node')
                ?? Arr::get($input, 'node_code')
                ?? Arr::get($input, 'node')
                ?? Arr::get($input, 'device_id')
                ?? Arr::get($input, 'device'),
            'node_id' => Arr::get($input, 'node_id'),
            'kelembaban_tanah' => Arr::get($input, 'kelembaban_tanah')
                ?? Arr::get($input, 'kelembaban')
                ?? Arr::get($input, 'soil')
                ?? Arr::get($input, 'soil_moisture')
                ?? Arr::get($input, 'moisture')
                ?? Arr::get($input, 'humidity'),
            'kelembaban_tanah_1' => Arr::get($input, 'kelembaban_tanah_1')
                ?? Arr::get($input, 'soil_1')
                ?? Arr::get($input, 'soil1')
                ?? Arr::get($input, 'soil_moisture_1')
                ?? Arr::get($input, 'soilMoisture1'),
            'kelembaban_tanah_2' => Arr::get($input, 'kelembaban_tanah_2')
                ?? Arr::get($input, 'soil_2')
                ?? Arr::get($input, 'soil2')
                ?? Arr::get($input, 'soil_moisture_2')
                ?? Arr::get($input, 'soilMoisture2'),
            'suhu' => Arr::get($input, 'suhu')
                ?? Arr::get($input, 'temperature')
                ?? Arr::get($input, 'temp')
                ?? Arr::get($input, 'temp_c'),
            'kelembaban_udara' => Arr::get($input, 'kelembaban_udara')
                ?? Arr::get($input, 'air_humidity')
                ?? Arr::get($input, 'humidity_air')
                ?? Arr::get($input, 'humidity_dht')
                ?? Arr::get($input, 'relative_humidity')
                ?? Arr::get($input, 'rh'),
            'ph_air' => Arr::get($input, 'ph_air')
                ?? Arr::get($input, 'ph')
                ?? Arr::get($input, 'water_ph')
                ?? Arr::get($input, 'phAir'),
            'debit_air' => Arr::get($input, 'debit_air')
                ?? Arr::get($input, 'debit')
                ?? Arr::get($input, 'flow')
                ?? Arr::get($input, 'water_flow')
                ?? Arr::get($input, 'flow_rate')
                ?? Arr::get($input, 'flowRate')
                ?? Arr::get($input, 'debit_lpm'),
        ];
    }

    private function averageSoil(null|int|float|string $soil1, null|int|float|string $soil2): ?float
    {
        $values = array_filter([$soil1, $soil2], fn ($value) => $value !== null && $value !== '');

        if ($values === []) {
            return null;
        }

        return round(array_sum(array_map('floatval', $values)) / count($values), 1);
    }

    private function findNode(?string $kodeNode, ?int $nodeId): ?Node
    {
        if ($nodeId) {
            return Node::find($nodeId);
        }

        if ($kodeNode) {
            return Node::where('kode_node', $kodeNode)->first();
        }

        return null;
    }
}
