<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\SensorReading;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class HistoryPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->validate([
            'node_id' => ['nullable', 'integer', 'exists:nodes,id'],
            'date' => ['nullable', 'date'],
        ]);

        $readings = SensorReading::with('node')
            ->when($filters['node_id'] ?? null, fn ($query, $nodeId) => $query->where('node_id', $nodeId))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('created_at', $date))
            ->latest()
            ->get();

        $node = isset($filters['node_id'])
            ? Node::find($filters['node_id'])
            : null;

        $pdf = Pdf::loadView('pdf.sensor-history', [
            'readings' => $readings,
            'node' => $node,
            'date' => $filters['date'] ?? null,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        $filename = 'riwayat-sensor-'.now()->format('Ymd-His').'.pdf';

        return $pdf->download($filename);
    }
}
