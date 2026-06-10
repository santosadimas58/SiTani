<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Riwayat Sensor</title>
    <style>
        @page {
            margin: 24px;
        }

        body {
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.45;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 4px;
        }

        .muted {
            color: #6b7280;
        }

        .meta {
            border: 1px solid #d1d5db;
            margin: 16px 0;
            padding: 10px 12px;
        }

        .meta table,
        .data-table {
            border-collapse: collapse;
            width: 100%;
        }

        .meta td {
            padding: 2px 8px 2px 0;
        }

        .data-table th {
            background: #14532d;
            color: #ffffff;
            font-weight: 700;
            text-align: left;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #d1d5db;
            padding: 7px 8px;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f3f4f6;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Riwayat Sensor</h1>
    <div class="muted">Laporan data sensor SiTani</div>

    <div class="meta">
        <table>
            <tr>
                <td><strong>Node</strong></td>
                <td>{{ $node?->nama_node ?? 'Semua Node' }}</td>
                <td><strong>Tanggal Filter</strong></td>
                <td>{{ $date ? \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') : 'Semua Tanggal' }}</td>
            </tr>
            <tr>
                <td><strong>Total Data</strong></td>
                <td>{{ $readings->count() }}</td>
                <td><strong>Dibuat</strong></td>
                <td>{{ $generatedAt->format('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Node</th>
                <th>Kelembaban Tanah</th>
                <th>Suhu</th>
                <th>Hum. Udara</th>
            </tr>
        </thead>
        <tbody>
            @forelse($readings as $reading)
            <tr>
                <td>{{ $reading->created_at->format('d/m/Y H:i:s') }}</td>
                <td>{{ $reading->node->nama_node ?? '-' }}</td>
                <td>{{ $reading->kelembaban_tanah ?? '-' }}%</td>
                <td>{{ $reading->suhu ?? '-' }} C</td>
                <td>{{ $reading->kelembaban_udara ?? '-' }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Belum ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
