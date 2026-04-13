<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\Monitoring;
use App\Livewire\History;
use App\Livewire\Pump;
use App\Livewire\Nodes;

Route::get('/', fn() => redirect('/dashboard'));

Route::get('/dashboard', Dashboard::class)->name('dashboard');
Route::get('/monitoring', Monitoring::class)->name('monitoring');
Route::get('/history', History::class)->name('history');
Route::get('/pump', Pump::class)->name('pump');
Route::get('/nodes', Nodes::class)->name('nodes');
