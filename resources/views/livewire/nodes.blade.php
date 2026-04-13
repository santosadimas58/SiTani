<div>
    <x-header title="Kelola Node" subtitle="Manajemen titik monitoring tanaman" separator>
        <x-slot:actions>
            <x-button label="+ Tambah Node" wire:click="openModal" class="btn-primary" icon="o-plus" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead>
                    <tr>
                        <th>#</th><th>Kode</th><th>Nama Node</th><th>Lokasi</th><th>Total Data</th><th>Status</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nodes as $node)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><span class="font-mono text-sm">{{ $node->kode_node }}</span></td>
                        <td class="font-medium">{{ $node->nama_node }}</td>
                        <td>{{ $node->lokasi ?? '-' }}</td>
                        <td>{{ $node->sensor_readings_count }} data</td>
                        <td>
                            <x-badge :value="$node->status" class="{{ $node->status === 'Aktif' ? 'badge-success' : 'badge-error' }}" />
                        </td>
                        <td class="flex gap-2">
                            <x-button label="Edit" wire:click="edit({{ $node->id }})" class="btn-sm btn-info" icon="o-pencil" />
                            <x-button label="Hapus" wire:click="delete({{ $node->id }})" wire:confirm="Yakin hapus node ini?" class="btn-sm btn-error" icon="o-trash" />
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 opacity-40">
                            <x-icon name="o-cpu-chip" class="w-10 h-10 mx-auto mb-2" />
                            <p>Belum ada node.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <x-modal wire:model="showModal" :title="$editMode ? 'Edit Node' : 'Tambah Node'" separator>
        <div class="flex flex-col gap-4">
            <x-input label="Kode Node" wire:model="kode_node" :disabled="$editMode" />
            <x-input label="Nama Node" wire:model="nama_node" placeholder="Contoh: Node Kebun A" />
            @error('nama_node') <span class="text-error text-xs">{{ $message }}</span> @enderror
            <x-input label="Lokasi" wire:model="lokasi" placeholder="Contoh: Greenhouse Lt.1" />
            <x-select label="Status" wire:model="status" :options="[['id'=>'Aktif','name'=>'Aktif'],['id'=>'Nonaktif','name'=>'Nonaktif']]" />
        </div>
        <x-slot:actions>
            <x-button label="Batal" wire:click="closeModal" icon="o-x-mark" />
            <x-button :label="$editMode ? 'Update' : 'Simpan'" wire:click="save" class="btn-primary" icon="o-check" />
        </x-slot:actions>
    </x-modal>
</div>
