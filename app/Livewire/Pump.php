<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Node;
use App\Models\PumpControl;

#[Layout('layouts.app')]
class Pump extends Component
{
    public function toggle($nodeId)
    {
        $last = PumpControl::where('node_id', $nodeId)->latest()->first();
        $newStatus = ($last && $last->status === 'ON') ? 'OFF' : 'ON';

        PumpControl::create([
            'node_id'      => $nodeId,
            'status'       => $newStatus,
            'triggered_by' => 'web',
        ]);

        $this->dispatch('mary-toast', toast: [
            'type' => $newStatus === 'ON' ? 'success' : 'error',
            'title' => 'Pompa ' . $newStatus,
            'description' => 'Status pompa berhasil diubah.',
            'position' => 'toast-top toast-end',
            'icon' => '', 'css' => $newStatus === 'ON' ? 'alert-success' : 'alert-error',
            'timeout' => 3000, 'noProgress' => false
        ]);
    }

    public function render()
    {
        $nodes = Node::with(['latestReading', 'latestPump'])->get();
        return view('livewire.pump', compact('nodes'));
    }
}
