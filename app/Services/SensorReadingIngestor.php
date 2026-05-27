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
            'suhu' => ['nullable', 'numeric', 'between:-40,100'],
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

        $reading = SensorReading::create([
            'node_id' => $node->id,
            'kelembaban_tanah' => $validated['kelembaban_tanah'] ?? null,
            'suhu' => $validated['suhu'] ?? null,
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
            'suhu' => Arr::get($input, 'suhu')
                ?? Arr::get($input, 'temperature')
                ?? Arr::get($input, 'temp')
                ?? Arr::get($input, 'temp_c'),
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
