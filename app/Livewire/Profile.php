<?php
namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

#[Layout('layouts.app')]
class Profile extends Component
{
    public $name = '';
    public $email = '';
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    public function mount()
    {
        $this->name  = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfile()
    {
        $this->validate([
            'name'  => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);

        Auth::user()->update(['name' => $this->name, 'email' => $this->email]);

        $this->dispatch('mary-toast', toast: [
            'type' => 'success', 'title' => 'Berhasil!',
            'description' => 'Profil berhasil diupdate.',
            'position' => 'toast-top toast-end',
            'icon' => '', 'css' => 'alert-success',
            'timeout' => 3000, 'noProgress' => false
        ]);
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        if (!Hash::check($this->current_password, Auth::user()->password)) {
            $this->addError('current_password', 'Password saat ini salah.');
            return;
        }

        Auth::user()->update(['password' => Hash::make($this->new_password)]);

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';

        $this->dispatch('mary-toast', toast: [
            'type' => 'success', 'title' => 'Berhasil!',
            'description' => 'Password berhasil diubah.',
            'position' => 'toast-top toast-end',
            'icon' => '', 'css' => 'alert-success',
            'timeout' => 3000, 'noProgress' => false
        ]);
    }

    public function render()
    {
        return view('livewire.profile');
    }
}
