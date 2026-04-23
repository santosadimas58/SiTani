<div class="hydrowatch-dashboard space-y-7">
    <section class="hydrowatch-hero">
        <div>
            <p class="hydrowatch-eyebrow">SiTani / Operations</p>
            <h1 class="hydrowatch-hero-title">Dashboard SiTani</h1>
            <p class="hydrowatch-hero-subtitle">Pantau node, kesehatan irigasi, dan aktivitas pompa dari satu panel yang rapi dan mudah dipindai.</p>
        </div>
        <div class="hydrowatch-hero-meta">
            <div class="hydrowatch-hero-chip">
                <span class="hydrowatch-live-dot"></span>
                Update setiap 5 detik
            </div>
            <div class="hydrowatch-hero-chip hydrowatch-hero-chip-soft">
                {{ $activeNodes }}/{{ $totalNodes }} node aktif
            </div>
            <a href="/monitoring" class="btn btn-primary hydrowatch-primary-button">Buka Monitoring</a>
        </div>
    </section>

    <section class="hydrowatch-command-card">
        <div>
            <p class="hydrowatch-command-label">Field health</p>
            <h2 class="hydrowatch-command-title">Irigasi berjalan dalam mode pemantauan aktif</h2>
            <p class="hydrowatch-command-copy">Gunakan metrik ringkas di bawah untuk melihat ketersediaan node, volume data, dan kebutuhan pengecekan lapangan.</p>
        </div>
        <div class="hydrowatch-command-grid">
            <div>
                <span>Node online</span>
                <strong>{{ $activeNodes }}</strong>
            </div>
            <div>
                <span>Total telemetry</span>
                <strong>{{ $totalReadings }}</strong>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <x-card class="hydrowatch-stat-card hydrowatch-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="hydrowatch-stat-label">Total Node</p>
                    <p class="hydrowatch-stat-value">{{ $totalNodes }}</p>
                    <p class="hydrowatch-stat-note">Perangkat terdaftar</p>
                </div>
                <div class="hydrowatch-stat-icon bg-primary/10">
                    <x-icon name="o-cpu-chip" class="w-6 h-6 text-primary" />
                </div>
            </div>
        </x-card>
        <x-card class="hydrowatch-stat-card hydrowatch-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="hydrowatch-stat-label">Node Aktif</p>
                    <p class="hydrowatch-stat-value">{{ $activeNodes }}</p>
                    <p class="hydrowatch-stat-note">Mengirim sinyal</p>
                </div>
                <div class="hydrowatch-stat-icon bg-success/10">
                    <x-icon name="o-signal" class="w-6 h-6 text-success" />
                </div>
            </div>
        </x-card>
        <x-card class="hydrowatch-stat-card hydrowatch-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="hydrowatch-stat-label">Total Data</p>
                    <p class="hydrowatch-stat-value">{{ $totalReadings }}</p>
                    <p class="hydrowatch-stat-note">Pembacaan sensor</p>
                </div>
                <div class="hydrowatch-stat-icon bg-info/10">
                    <x-icon name="o-chart-bar" class="w-6 h-6 text-info" />
                </div>
            </div>
        </x-card>
        <x-card class="hydrowatch-stat-card hydrowatch-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="hydrowatch-stat-label">Refresh Rate</p>
                    <p class="hydrowatch-stat-value">5 detik</p>
                    <p class="hydrowatch-stat-note">Polling realtime</p>
                </div>
                <div class="hydrowatch-stat-icon bg-warning/10">
                    <x-icon name="o-clock" class="w-6 h-6 text-warning" />
                </div>
            </div>
        </x-card>
    </div>

    {{-- Node Cards --}}
    <section class="space-y-4">
        <div class="hydrowatch-section-head">
            <div>
                <h2 class="hydrowatch-section-title">Status Node</h2>
                <p class="hydrowatch-section-subtitle">Ringkasan kondisi tiap titik irigasi dengan data sensor terbaru dan status pompa.</p>
            </div>
        </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
        @forelse($nodes as $node)
        @php $reading = $node->latestReading; $pump = $node->latestPump; @endphp
        <x-card class="hydrowatch-card hydrowatch-node-card">
            <div class="flex justify-between items-start gap-4 mb-5">
                <div>
                    <p class="text-lg font-semibold text-slate-900">{{ $node->nama_node }}</p>
                    <p class="text-sm text-slate-500 mt-1">{{ $node->kode_node }} · {{ $node->lokasi ?? '-' }}</p>
                </div>
                <x-badge :value="$node->status" class="hydrowatch-status-badge {{ $node->status === 'Aktif' ? 'badge-success' : 'badge-error' }}" />
            </div>
            @if($reading)
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="hydrowatch-sensor-tile bg-green-50/80">
                    <p class="hydrowatch-sensor-label">Kelembaban</p>
                    <p class="font-bold text-primary">{{ $reading->kelembaban_tanah ?? '-' }}%</p>
                </div>
                <div class="hydrowatch-sensor-tile bg-amber-50">
                    <p class="hydrowatch-sensor-label">Suhu</p>
                    <p class="font-bold text-warning">{{ $reading->suhu ?? '-' }}°C</p>
                </div>
                <div class="hydrowatch-sensor-tile bg-sky-50">
                    <p class="hydrowatch-sensor-label">pH Air</p>
                    <p class="font-bold text-info">{{ $reading->ph_air ?? '-' }}</p>
                </div>
                <div class="hydrowatch-sensor-tile bg-emerald-50">
                    <p class="hydrowatch-sensor-label">Debit Air</p>
                    <p class="font-bold text-secondary">{{ $reading->debit_air ?? '-' }} L/m</p>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-4 text-right">{{ $reading->created_at->diffForHumans() }}</p>
            @else
            <div class="hydrowatch-empty-block">
                <div class="hydrowatch-empty-icon">
                    <x-icon name="o-signal-slash" class="w-8 h-8" />
                </div>
                <p class="hydrowatch-empty-title">Belum ada data sensor</p>
                <p class="hydrowatch-empty-copy">Node ini belum mengirim pembacaan terbaru. Periksa koneksi perangkat atau buka menu monitoring.</p>
                <a href="/monitoring" class="btn btn-primary hydrowatch-primary-button">Lihat Monitoring</a>
            </div>
            @endif
            <div class="divider my-3"></div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-slate-600">Status Pompa</span>
                <x-badge :value="$pump ? $pump->status : 'OFF'" class="hydrowatch-status-badge {{ ($pump && $pump->status === 'ON') ? 'badge-success' : 'badge-error' }}" />
            </div>
        </x-card>
        @empty
        <div class="xl:col-span-3">
            <div class="hydrowatch-empty-state">
                <div class="hydrowatch-empty-icon">
                    <x-icon name="o-cpu-chip" class="w-12 h-12" />
                </div>
                <p class="hydrowatch-empty-title">Belum ada node terdaftar</p>
                <p class="hydrowatch-empty-copy">Tambahkan node baru agar dashboard mulai menampilkan data sensor, status koneksi, dan kontrol pompa.</p>
                <a href="/nodes" class="btn btn-primary hydrowatch-primary-button">Kelola Node</a>
            </div>
        </div>
        @endforelse
    </div>
    </section>

    {{-- Latest Readings --}}
    <section class="space-y-4">
        <div class="hydrowatch-section-head">
            <div>
                <h2 class="hydrowatch-section-title">Data Terbaru</h2>
                <p class="hydrowatch-section-subtitle">Snapshot pembacaan terbaru dari seluruh node untuk membantu validasi kondisi lapangan dengan cepat.</p>
            </div>
        </div>

    <x-card class="hydrowatch-card hydrowatch-table-card">
        <div class="overflow-x-auto">
            <table class="table w-full text-sm hydrowatch-table">
                <thead>
                    <tr>
                        <th>Waktu</th><th>Node</th><th>Kelembaban</th><th>Suhu</th><th>pH</th><th>Debit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestReadings as $r)
                    <tr>
                        <td class="text-xs text-slate-500">{{ $r->created_at->format('d/m H:i:s') }}</td>
                        <td class="font-medium text-slate-800">{{ $r->node->nama_node ?? '-' }}</td>
                        <td>{{ $r->kelembaban_tanah ?? '-' }}%</td>
                        <td>{{ $r->suhu ?? '-' }}°C</td>
                        <td>{{ $r->ph_air ?? '-' }}</td>
                        <td>{{ $r->debit_air ?? '-' }} L/m</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8">
                            <div class="hydrowatch-empty-inline">
                                <div class="hydrowatch-empty-icon">
                                    <x-icon name="o-clock" class="w-8 h-8" />
                                </div>
                                <p class="hydrowatch-empty-title">Belum ada data terbaru</p>
                                <p class="hydrowatch-empty-copy">Ketika node mulai mengirim pembacaan, ringkasan terbaru akan tampil di sini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
    </section>
</div>
