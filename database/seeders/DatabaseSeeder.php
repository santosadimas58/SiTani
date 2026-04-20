<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@hydrowatch.com'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );
    }
}
