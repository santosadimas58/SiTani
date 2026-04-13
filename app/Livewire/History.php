<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\SensorReading;
use App\Models\Node;

#[Layout('layouts.app')]
class History extends Component
{
    use WithPagination;

    public $filterNode = '';
    public $filterDate = '';

    public function updatedFilterNode() { $this->resetPage(); }
    public function updatedFilterDate() { $this->resetPage(); }

    public function render()
    {
        $readings = SensorReading::with('node')
            ->when($this->filterNode, fn($q) => $q->where('node_id', $this->filterNode))
            ->when($this->filterDate, fn($q) => $q->whereDate('created_at', $this->filterDate))
            ->latest()
            ->paginate(15);

        $nodes = Node::all();
        return view('livewire.history', compact('readings', 'nodes'));
    }
}
