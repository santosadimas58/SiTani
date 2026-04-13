<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Node;

#[Layout('layouts.app')]
class Nodes extends Component
{
    public $showModal = false;
    public $editMode = false;
    public $nodeId;
    public $kode_node = '';
    public $nama_node = '';
    public $lokasi = '';
    public $status = 'Aktif';

    public function openModal()
    {
        $this->resetFields();
        $this->editMode = false;
        $this->kode_node = 'NODE-' . str_pad(Node::count() + 1, 2, '0', STR_PAD_LEFT);
        $this->showModal = true;
    }

    public function closeModal() { $this->showModal = false; $this->resetFields(); }

    public function resetFields()
    {
        $this->nodeId = null;
        $this->kode_node = '';
        $this->nama_node = '';
        $this->lokasi = '';
        $this->status = 'Aktif';
    }

    public function save()
    {
        $this->validate([
            'nama_node' => 'required|min:3',
            'status'    => 'required',
        ]);

        if ($this->editMode) {
            Node::find($this->nodeId)->update([
                'nama_node' => $this->nama_node,
                'lokasi'    => $this->lokasi,
                'status'    => $this->status,
            ]);
        } else {
            Node::create([
                'kode_node' => $this->kode_node,
                'nama_node' => $this->nama_node,
                'lokasi'    => $this->lokasi,
                'status'    => $this->status,
            ]);
        }

        $this->dispatch('mary-toast', toast: [
            'type' => 'success', 'title' => 'Berhasil!',
            'description' => 'Data node berhasil disimpan.',
            'position' => 'toast-top toast-end',
            'icon' => '', 'css' => 'alert-success',
            'timeout' => 3000, 'noProgress' => false
        ]);

        $this->closeModal();
    }

    public function edit($id)
    {
        $node = Node::find($id);
        $this->nodeId = $node->id;
        $this->kode_node = $node->kode_node;
        $this->nama_node = $node->nama_node;
        $this->lokasi = $node->lokasi;
        $this->status = $node->status;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function delete($id)
    {
        Node::find($id)->delete();
        $this->dispatch('mary-toast', toast: [
            'type' => 'error', 'title' => 'Dihapus!',
            'description' => 'Node berhasil dihapus.',
            'position' => 'toast-top toast-end',
            'icon' => '', 'css' => 'alert-error',
            'timeout' => 3000, 'noProgress' => false
        ]);
    }

    public function render()
    {
        $nodes = Node::withCount('sensorReadings')->latest()->get();
        return view('livewire.nodes', compact('nodes'));
    }
}
