<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\PumpControl;
use App\Services\MqttBroker;
use App\Services\SensorReadingIngestor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SensorController extends Controller
{
    public function health()
    {
        return response()->json([
            'success' => true,
            'app' => config('app.name'),
            'time' => now()->toIso8601String(),
        ]);
    }

    // ESP32 kirim data sensor ke sini
    // POST /api/sensor
    public function store(Request $request, SensorReadingIngestor $ingestor)
    {
        $result = $ingestor->ingest($request->all());

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
            ], 404);
        }

        $node = $result['node'];
        $reading = $result['reading'];

        return response()->json([
            'success' => true,
            'message' => 'Data sensor tersimpan',
            'node' => [
                'id' => $node->id,
                'kode_node' => $node->kode_node,
                'nama_node' => $node->nama_node,
            ],
            'reading_id' => $reading->id,
            'received_at' => $reading->created_at->toIso8601String(),
            'pump_status' => $result['pump_status'],
            'pump_on' => $result['pump_on'],
        ], 201);
    }

    // ESP32 cek status pompa
    // GET /api/pump/{node}
    public function pumpStatus($node)
    {
        $node = $this->findNode($node, is_numeric($node) ? (int) $node : null);

        if (! $node) {
            return response()->json([
                'success' => false,
                'error' => 'Node tidak ditemukan',
            ], 404);
        }

        $pump = PumpControl::where('node_id', $node->id)->latest()->first();
        $pumpStatus = $pump ? $pump->status : 'OFF';

        return response()->json([
            'success' => true,
            'kode_node' => $node->kode_node,
            'pump_status' => $pumpStatus,
            'pump_on' => $pumpStatus === 'ON',
        ]);
    }

    // Web toggle pompa ON/OFF
    // POST /api/pump/toggle
    public function togglePump(Request $request, MqttBroker $mqtt)
    {
        $request->validate(['node_id' => 'required|exists:nodes,id']);

        $node = Node::findOrFail($request->node_id);
        $lastPump = PumpControl::where('node_id', $node->id)->latest()->first();
        $newStatus = ($lastPump && $lastPump->status === 'ON') ? 'OFF' : 'ON';

        PumpControl::create([
            'node_id' => $node->id,
            'status' => $newStatus,
            'triggered_by' => 'web',
        ]);

        try {
            $mqtt->publishPumpStatus($node, $newStatus);
        } catch (\Throwable $exception) {
            Log::warning('Failed to publish pump status to MQTT', [
                'node_id' => $node->id,
                'status' => $newStatus,
                'error' => $exception->getMessage(),
            ]);
        }

        return response()->json(['success' => true, 'pump_status' => $newStatus]);
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
