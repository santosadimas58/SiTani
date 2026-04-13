<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Node;
use App\Models\SensorReading;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $nodes = Node::with(['latestReading', 'latestPump'])->get();
        $totalNodes = $nodes->count();
        $activeNodes = $nodes->where('status', 'Aktif')->count();
        $totalReadings = SensorReading::count();
        $latestReadings = SensorReading::with('node')->latest()->take(5)->get();

        return view('livewire.dashboard', compact(
            'nodes', 'totalNodes', 'activeNodes', 'totalReadings', 'latestReadings'
        ));
    }
}
