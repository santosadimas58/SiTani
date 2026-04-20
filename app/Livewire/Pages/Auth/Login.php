<?php
namespace App\Livewire\Pages\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.guest')]
class Login extends Component
{
    public $email = '';
    public $password = '';

    public function authenticate()
    {
        $this->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            $this->addError('email', 'Email atau password salah.');
            return;
        }

        session()->regenerate();
        return redirect('/dashboard');
    }

    public function render()
    {
        return view('livewire.pages.auth.login');
    }
}
