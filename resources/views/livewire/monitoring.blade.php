<div>
    <x-header title="Monitoring" subtitle="Data sensor semua node secara realtime" separator />
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @forelse($nodes as $node)
        @php $reading = $node->latestReading; $pump = $node->latestPump; @endphp
        <x-card class="shadow" wire:poll.5s>
            <div class="flex justify-between items-center mb-4">
                <div>
                    <p class="font-bold">{{ $node->nama_node }}</p>
                    <p class="text-xs opacity-50">{{ $node->kode_node }}</p>
                </div>
                <x-badge :value="$node->status" class="{{ $node->status === 'Aktif' ? 'badge-success' : 'badge-ghost' }}" />
            </div>
            @if($reading)
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm opacity-60">💧 Kelembaban Tanah</span>
                    <span class="font-bold text-primary">{{ $reading->kelembaban_tanah ?? '-' }}%</span>
                </div>
                <progress class="progress progress-primary w-full" value="{{ $reading->kelembaban_tanah ?? 0 }}" max="100"></progress>

                <div class="flex justify-between items-center">
                    <span class="text-sm opacity-60">🌡️ Suhu</span>
                    <span class="font-bold text-warning">{{ $reading->suhu ?? '-' }}°C</span>
                </div>
                <progress class="progress progress-warning w-full" value="{{ $reading->suhu ?? 0 }}" max="50"></progress>

                <div class="flex justify-between items-center">
                    <span class="text-sm opacity-60">🌫️ Kelembaban Udara</span>
                    <span class="font-bold text-info">{{ $reading->kelembaban_udara ?? '-' }}%</span>
                </div>
                <progress class="progress progress-info w-full" value="{{ $reading->kelembaban_udara ?? 0 }}" max="100"></progress>
            </div>
            <p class="text-xs opacity-30 mt-3 text-right">{{ $reading->created_at->diffForHumans() }}</p>
            @else
            <div class="text-center py-6 opacity-40">
                <x-icon name="o-signal-slash" class="w-10 h-10 mx-auto mb-2" />
                <p class="text-sm">Menunggu data sensor...</p>
            </div>
            @endif
            <div class="divider my-2"></div>
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium">Pompa</span>
                <x-badge :value="$pump ? $pump->status : 'OFF'" class="{{ ($pump && $pump->status === 'ON') ? 'badge-success' : 'badge-error' }}" />
            </div>
        </x-card>
        @empty
        <div class="col-span-3 text-center py-12 opacity-40">
            <x-icon name="o-cpu-chip" class="w-12 h-12 mx-auto mb-2" />
            <p>Belum ada node terdaftar.</p>
        </div>
        @endforelse
    </div>
</div>
