<div class="sitani-dashboard space-y-7">
    <section class="sitani-hero">
        <div>
            <p class="sitani-eyebrow">SiTani / Operations</p>
            <h1 class="sitani-hero-title">Dashboard SiTani</h1>
            <p class="sitani-hero-subtitle">Pantau node, kesehatan irigasi, dan aktivitas pompa dari satu panel yang rapi dan mudah dipindai.</p>
        </div>
        <div class="sitani-hero-meta">
            <div class="sitani-hero-chip">
                <span class="sitani-live-dot"></span>
                Update setiap 5 detik
            </div>
            <div class="sitani-hero-chip sitani-hero-chip-soft">
                {{ $activeNodes }}/{{ $totalNodes }} node aktif
            </div>
            <button
                type="button"
                class="sitani-theme-toggle sitani-dashboard-theme-toggle"
                x-data="{
                    darkTheme: JSON.parse(localStorage.getItem('sitani-dark-theme') ?? 'false'),
                    init() {
                        this.syncTheme();
                    },
                    syncTheme() {
                        document.documentElement.dataset.theme = this.darkTheme ? 'dark' : 'light';
                        document.body.classList.toggle('sitani-dark', this.darkTheme);
                    },
                    toggleTheme() {
                        this.darkTheme = !this.darkTheme;
                        localStorage.setItem('sitani-dark-theme', JSON.stringify(this.darkTheme));
                        this.syncTheme();
                    }
                }"
                @click="toggleTheme()"
                x-bind:aria-label="darkTheme ? 'Gunakan tema terang' : 'Gunakan tema gelap'"
            >
                <x-icon name="o-sun" class="w-5 h-5" x-show="darkTheme" />
                <x-icon name="o-moon" class="w-5 h-5" x-show="!darkTheme" />
                <span x-text="darkTheme ? 'Terang' : 'Gelap'"></span>
            </button>
            <a href="/monitoring" class="btn btn-primary sitani-primary-button">Buka Monitoring</a>
        </div>
    </section>

    <section class="sitani-command-card">
        <div>
            <p class="sitani-command-label">Field health</p>
            <h2 class="sitani-command-title">Irigasi berjalan dalam mode pemantauan aktif</h2>
            <p class="sitani-command-copy">Gunakan metrik ringkas di bawah untuk melihat ketersediaan node, volume data, dan kebutuhan pengecekan lapangan.</p>
        </div>
        <div class="sitani-command-grid">
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
        <x-card class="sitani-stat-card sitani-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="sitani-stat-label">Total Node</p>
                    <p class="sitani-stat-value">{{ $totalNodes }}</p>
                    <p class="sitani-stat-note">Perangkat terdaftar</p>
                </div>
                <div class="sitani-stat-icon bg-primary/10">
                    <x-icon name="o-cpu-chip" class="w-6 h-6 text-primary" />
                </div>
            </div>
        </x-card>
        <x-card class="sitani-stat-card sitani-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="sitani-stat-label">Node Aktif</p>
                    <p class="sitani-stat-value">{{ $activeNodes }}</p>
                    <p class="sitani-stat-note">Mengirim sinyal</p>
                </div>
                <div class="sitani-stat-icon bg-success/10">
                    <x-icon name="o-signal" class="w-6 h-6 text-success" />
                </div>
            </div>
        </x-card>
        <x-card class="sitani-stat-card sitani-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="sitani-stat-label">Total Data</p>
                    <p class="sitani-stat-value">{{ $totalReadings }}</p>
                    <p class="sitani-stat-note">Pembacaan sensor</p>
                </div>
                <div class="sitani-stat-icon bg-info/10">
                    <x-icon name="o-chart-bar" class="w-6 h-6 text-info" />
                </div>
            </div>
        </x-card>
        <x-card class="sitani-stat-card sitani-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="sitani-stat-label">Refresh Rate</p>
                    <p class="sitani-stat-value">5 detik</p>
                    <p class="sitani-stat-note">Polling realtime</p>
                </div>
                <div class="sitani-stat-icon bg-warning/10">
                    <x-icon name="o-clock" class="w-6 h-6 text-warning" />
                </div>
            </div>
        </x-card>
    </div>

    {{-- Node Cards --}}
    <section class="space-y-4">
        <div class="sitani-section-head">
            <div>
                <h2 class="sitani-section-title">Status Node</h2>
                <p class="sitani-section-subtitle">Ringkasan kondisi tiap titik irigasi dengan data sensor terbaru dan status pompa.</p>
            </div>
        </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
        @forelse($nodes as $node)
        @php $reading = $node->latestReading; $pump = $node->latestPump; @endphp
        <x-card class="sitani-card sitani-node-card">
            <div class="flex justify-between items-start gap-4 mb-5">
                <div>
                    <p class="text-lg font-semibold text-slate-900">{{ $node->nama_node }}</p>
                    <p class="text-sm text-slate-500 mt-1">{{ $node->kode_node }} · {{ $node->lokasi ?? '-' }}</p>
                </div>
                <x-badge :value="$node->status" class="sitani-status-badge {{ $node->status === 'Aktif' ? 'badge-success' : 'badge-error' }}" />
            </div>
            @if($reading)
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div class="sitani-sensor-tile bg-green-50/80">
                    <p class="sitani-sensor-label">Kelembaban Tanah</p>
                    <p class="font-bold text-primary">{{ $reading->kelembaban_tanah ?? '-' }}%</p>
                </div>
                <div class="sitani-sensor-tile bg-amber-50">
                    <p class="sitani-sensor-label">Suhu</p>
                    <p class="font-bold text-warning">{{ $reading->suhu ?? '-' }}°C</p>
                </div>
                <div class="sitani-sensor-tile bg-sky-50">
                    <p class="sitani-sensor-label">Hum. Udara</p>
                    <p class="font-bold text-info">{{ $reading->kelembaban_udara ?? '-' }}%</p>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-4 text-right">{{ $reading->created_at->diffForHumans() }}</p>
            @else
            <div class="sitani-empty-block">
                <div class="sitani-empty-icon">
                    <x-icon name="o-signal-slash" class="w-8 h-8" />
                </div>
                <p class="sitani-empty-title">Belum ada data sensor</p>
                <p class="sitani-empty-copy">Node ini belum mengirim pembacaan terbaru. Periksa koneksi perangkat atau buka menu monitoring.</p>
                <a href="/monitoring" class="btn btn-primary sitani-primary-button">Lihat Monitoring</a>
            </div>
            @endif
            <div class="divider my-3"></div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-slate-600">Status Pompa</span>
                <x-badge :value="$pump ? $pump->status : 'OFF'" class="sitani-status-badge {{ ($pump && $pump->status === 'ON') ? 'badge-success' : 'badge-error' }}" />
            </div>
        </x-card>
        @empty
        <div class="xl:col-span-3">
            <div class="sitani-empty-state">
                <div class="sitani-empty-icon">
                    <x-icon name="o-cpu-chip" class="w-12 h-12" />
                </div>
                <p class="sitani-empty-title">Belum ada node terdaftar</p>
                <p class="sitani-empty-copy">Tambahkan node baru agar dashboard mulai menampilkan data sensor, status koneksi, dan kontrol pompa.</p>
                <a href="/nodes" class="btn btn-primary sitani-primary-button">Kelola Node</a>
            </div>
        </div>
        @endforelse
    </div>
    </section>

    {{-- Latest Readings --}}
    <section class="space-y-4">
        <div class="sitani-section-head">
            <div>
                <h2 class="sitani-section-title">Data Terbaru</h2>
                <p class="sitani-section-subtitle">Snapshot pembacaan terbaru dari seluruh node untuk membantu validasi kondisi lapangan dengan cepat.</p>
            </div>
        </div>

    <x-card class="sitani-card sitani-table-card">
        <div class="overflow-x-auto">
            <table class="table w-full text-sm sitani-table">
                <thead>
                    <tr>
                        <th>Waktu</th><th>Node</th><th>Kelembaban Tanah</th><th>Suhu</th><th>Hum. Udara</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestReadings as $r)
                    <tr>
                        <td class="text-xs text-slate-500">{{ $r->created_at->format('d/m H:i:s') }}</td>
                        <td class="font-medium text-slate-800">{{ $r->node->nama_node ?? '-' }}</td>
                        <td>{{ $r->kelembaban_tanah ?? '-' }}%</td>
                        <td>{{ $r->suhu ?? '-' }}°C</td>
                        <td>{{ $r->kelembaban_udara ?? '-' }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8">
                            <div class="sitani-empty-inline">
                                <div class="sitani-empty-icon">
                                    <x-icon name="o-clock" class="w-8 h-8" />
                                </div>
                                <p class="sitani-empty-title">Belum ada data terbaru</p>
                                <p class="sitani-empty-copy">Ketika node mulai mengirim pembacaan, ringkasan terbaru akan tampil di sini.</p>
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
