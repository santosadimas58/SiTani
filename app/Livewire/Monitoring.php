<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Node;

#[Layout('layouts.app')]
class Monitoring extends Component
{
    public $selectedNode = null;

    public function render()
    {
        $nodes = Node::with(['latestReading', 'latestPump'])->get();
        return view('livewire.monitoring', compact('nodes'));
    }
}
