<?php
use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use App\Livewire\Monitoring;
use App\Livewire\History;
use App\Livewire\Pump;
use App\Livewire\Nodes;
use App\Livewire\Profile;
use App\Livewire\Pages\Auth\Login;

Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : view('welcome');
})->name('home');

Route::get('/login', Login::class)->name('login');
Route::get('/logout', function () {
    auth()->logout();
    session()->invalidate();
    return redirect('/login');
})->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/monitoring', Monitoring::class)->name('monitoring');
    Route::get('/history', History::class)->name('history');
    Route::get('/pump', Pump::class)->name('pump');
    Route::get('/nodes', Nodes::class)->name('nodes');
    Route::get('/profile', Profile::class)->name('profile');
});
