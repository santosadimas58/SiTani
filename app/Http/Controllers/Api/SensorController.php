<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\SensorReading;
use App\Models\PumpControl;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    // ESP32 kirim data sensor ke sini
    // POST /api/sensor
    public function store(Request $request)
    {
        $request->validate([
            'kode_node'        => 'required|string',
            'kelembaban_tanah' => 'nullable|numeric',
            'suhu'             => 'nullable|numeric',
            'ph_air'           => 'nullable|numeric',
            'debit_air'        => 'nullable|numeric',
        ]);

        $node = Node::where('kode_node', $request->kode_node)->first();

        if (!$node) {
            return response()->json(['error' => 'Node tidak ditemukan'], 404);
        }

        SensorReading::create([
            'node_id'          => $node->id,
            'kelembaban_tanah' => $request->kelembaban_tanah,
            'suhu'             => $request->suhu,
            'ph_air'           => $request->ph_air,
            'debit_air'        => $request->debit_air,
        ]);

        // Kembalikan status pompa terbaru ke ESP32
        $pump = PumpControl::where('node_id', $node->id)->latest()->first();

        return response()->json([
            'success'     => true,
            'pump_status' => $pump ? $pump->status : 'OFF',
        ]);
    }

    // ESP32 cek status pompa
    // GET /api/pump/{kode_node}
    public function pumpStatus($kode_node)
    {
        $node = Node::where('kode_node', $kode_node)->first();

        if (!$node) {
            return response()->json(['error' => 'Node tidak ditemukan'], 404);
        }

        $pump = PumpControl::where('node_id', $node->id)->latest()->first();

        return response()->json([
            'kode_node'   => $kode_node,
            'pump_status' => $pump ? $pump->status : 'OFF',
        ]);
    }

    // Web toggle pompa ON/OFF
    // POST /api/pump/toggle
    public function togglePump(Request $request)
    {
        $request->validate(['node_id' => 'required|exists:nodes,id']);

        $lastPump = PumpControl::where('node_id', $request->node_id)->latest()->first();
        $newStatus = ($lastPump && $lastPump->status === 'ON') ? 'OFF' : 'ON';

        PumpControl::create([
            'node_id'      => $request->node_id,
            'status'       => $newStatus,
            'triggered_by' => 'web',
        ]);

        return response()->json(['success' => true, 'pump_status' => $newStatus]);
    }
}
