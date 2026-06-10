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
            ['email' => 'admin@sitani.com'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@sitani.com'],
            [
                'name'     => 'Example User',
                'password' => Hash::make('password'),
            ]
        );
    }
}
