<div>
    <x-header title="Kontrol Pompa" subtitle="Nyalakan atau matikan pompa tiap node" separator />

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @forelse($nodes as $node)
        @php $pump = $node->latestPump; $isOn = $pump && $pump->status === 'ON'; @endphp
        <x-card class="shadow text-center">
            <x-icon name="o-cpu-chip" class="w-10 h-10 mx-auto mb-2 {{ $isOn ? 'text-success' : 'text-error' }}" />
            <p class="font-bold text-lg">{{ $node->nama_node }}</p>
            <p class="text-xs opacity-50 mb-4">{{ $node->lokasi ?? '-' }}</p>

            <div class="mb-4">
                <x-badge
                    :value="$isOn ? 'POMPA ON' : 'POMPA OFF'"
                    class="badge-lg {{ $isOn ? 'badge-success' : 'badge-error' }}"
                />
            </div>

            <x-button
                :label="$isOn ? 'Matikan Pompa' : 'Nyalakan Pompa'"
                wire:click="toggle({{ $node->id }})"
                class="w-full {{ $isOn ? 'btn-error' : 'btn-success' }}"
                :icon="$isOn ? 'o-stop' : 'o-play'"
            />

            @if($pump)
            <p class="text-xs opacity-30 mt-2">Terakhir diubah {{ $pump->created_at->diffForHumans() }}</p>
            @endif
        </x-card>
        @empty
        <div class="col-span-3 text-center py-12 opacity-40">
            <x-icon name="o-bolt" class="w-12 h-12 mx-auto mb-2" />
            <p>Belum ada node terdaftar.</p>
        </div>
        @endforelse
    </div>
</div>
