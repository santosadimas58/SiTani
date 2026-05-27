<div>
    <x-header title="Riwayat Sensor" subtitle="Log data sensor semua node" separator />

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex flex-wrap gap-3">
            <x-select
                placeholder="Semua Node"
                wire:model.live="filterNode"
                :options="$nodes->map(fn($n) => ['id' => $n->id, 'name' => $n->nama_node])->toArray()"
                class="w-48"
            />
            <x-input type="date" wire:model.live="filterDate" class="w-48" />
            @if($filterNode || $filterDate)
            <x-button label="Reset" wire:click="$set('filterNode', ''); $set('filterDate', '')" class="btn-ghost btn-sm" icon="o-x-mark" />
            @endif
        </div>

        <a
            href="{{ route('history.export-pdf', array_filter(['node_id' => $filterNode, 'date' => $filterDate])) }}"
            class="btn btn-primary btn-sm"
            target="_blank"
        >
            <x-icon name="o-document-arrow-down" class="w-4 h-4" />
            Export PDF
        </a>
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full text-sm">
                <thead>
                    <tr>
                        <th>Waktu</th><th>Node</th><th>Kelembaban</th><th>Suhu</th><th>pH</th><th>Debit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($readings as $r)
                    <tr>
                        <td class="text-xs">{{ $r->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="font-medium">{{ $r->node->nama_node ?? '-' }}</td>
                        <td>{{ $r->kelembaban_tanah ?? '-' }}%</td>
                        <td>{{ $r->suhu ?? '-' }}°C</td>
                        <td>{{ $r->ph_air ?? '-' }}</td>
                        <td>{{ $r->debit_air ?? '-' }} L/m</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-6 opacity-40">Belum ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $readings->links() }}</div>
    </x-card>
</div>
