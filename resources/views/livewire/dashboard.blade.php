<div>
    <x-header title="Dashboard HydroWatch" subtitle="Monitoring pengairan tanaman realtime" separator />

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-stat title="Total Node" :value="$totalNodes" icon="o-cpu-chip" color="text-primary" />
        <x-stat title="Node Aktif" :value="$activeNodes" icon="o-signal" color="text-success" />
        <x-stat title="Total Data" :value="$totalReadings" icon="o-chart-bar" color="text-info" />
        <x-stat title="Update" value="5 detik" icon="o-clock" color="text-warning" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        @forelse($nodes as $node)
        @php $reading = $node->latestReading; $pump = $node->latestPump; @endphp
        <x-card class="shadow">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <p class="font-bold text-lg">{{ $node->nama_node }}</p>
                    <p class="text-xs opacity-50">{{ $node->kode_node }} · {{ $node->lokasi ?? '-' }}</p>
                </div>
                <x-badge :value="$node->status" class="{{ $node->status === 'Aktif' ? 'badge-success' : 'badge-error' }}" />
            </div>
            @if($reading)
            <div class="grid grid-cols-2 gap-2 text-sm">
                <div class="bg-base-200 rounded-lg p-2 text-center">
                    <p class="text-xs opacity-50">Kelembaban</p>
                    <p class="font-bold text-primary">{{ $reading->kelembaban_tanah ?? '-' }}%</p>
                </div>
                <div class="bg-base-200 rounded-lg p-2 text-center">
                    <p class="text-xs opacity-50">Suhu</p>
                    <p class="font-bold text-warning">{{ $reading->suhu ?? '-' }}°C</p>
                </div>
                <div class="bg-base-200 rounded-lg p-2 text-center">
                    <p class="text-xs opacity-50">pH Air</p>
                    <p class="font-bold text-info">{{ $reading->ph_air ?? '-' }}</p>
                </div>
                <div class="bg-base-200 rounded-lg p-2 text-center">
                    <p class="text-xs opacity-50">Debit Air</p>
                    <p class="font-bold text-secondary">{{ $reading->debit_air ?? '-' }} L/m</p>
                </div>
            </div>
            <p class="text-xs opacity-30 mt-2 text-right">{{ $reading->created_at->diffForHumans() }}</p>
            @else
            <div class="text-center py-4 opacity-40">
                <x-icon name="o-signal-slash" class="w-8 h-8 mx-auto mb-1" />
                <p class="text-xs">Belum ada data sensor</p>
            </div>
            @endif
            <div class="divider my-2"></div>
            <div class="flex justify-between items-center">
                <span class="text-sm">Status Pompa</span>
                <x-badge :value="$pump ? $pump->status : 'OFF'" class="{{ ($pump && $pump->status === 'ON') ? 'badge-success' : 'badge-error' }}" />
            </div>
        </x-card>
        @empty
        <div class="col-span-3 text-center py-12 opacity-40">
            <x-icon name="o-cpu-chip" class="w-12 h-12 mx-auto mb-2" />
            <p>Belum ada node. Tambahkan di menu Kelola Node.</p>
        </div>
        @endforelse
    </div>

    <x-card title="Data Terbaru" icon="o-clock" separator>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full text-sm">
                <thead>
                    <tr>
                        <th>Waktu</th><th>Node</th><th>Kelembaban</th><th>Suhu</th><th>pH</th><th>Debit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestReadings as $r)
                    <tr>
                        <td class="text-xs opacity-60">{{ $r->created_at->format('d/m H:i:s') }}</td>
                        <td class="font-medium">{{ $r->node->nama_node ?? '-' }}</td>
                        <td>{{ $r->kelembaban_tanah ?? '-' }}%</td>
                        <td>{{ $r->suhu ?? '-' }}°C</td>
                        <td>{{ $r->ph_air ?? '-' }}</td>
                        <td>{{ $r->debit_air ?? '-' }} L/m</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 opacity-40">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
