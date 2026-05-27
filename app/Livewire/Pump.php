<?php

namespace App\Livewire;

use App\Models\Node;
use App\Models\PumpControl;
use App\Services\MqttBroker;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Pump extends Component
{
    public function toggle($nodeId)
    {
        $mqtt = app(MqttBroker::class);
        $node = Node::findOrFail($nodeId);
        $last = PumpControl::where('node_id', $node->id)->latest()->first();
        $newStatus = ($last && $last->status === 'ON') ? 'OFF' : 'ON';

        PumpControl::create([
            'node_id' => $node->id,
            'status' => $newStatus,
            'triggered_by' => 'web',
        ]);

        try {
            $mqtt->publishPumpStatus($node, $newStatus);
        } catch (\Throwable $exception) {
            Log::warning('Failed to publish pump status to MQTT', [
                'node_id' => $node->id,
                'status' => $newStatus,
                'error' => $exception->getMessage(),
            ]);
        }

        $this->dispatch('mary-toast', toast: [
            'type' => $newStatus === 'ON' ? 'success' : 'error',
            'title' => 'Pompa '.$newStatus,
            'description' => 'Status pompa berhasil diubah.',
            'position' => 'toast-top toast-end',
            'icon' => '', 'css' => $newStatus === 'ON' ? 'alert-success' : 'alert-error',
            'timeout' => 3000, 'noProgress' => false,
        ]);
    }

    public function render()
    {
        $nodes = Node::with(['latestReading', 'latestPump'])->get();

        return view('livewire.pump', compact('nodes'));
    }
}
