<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreatePlayerAccountSeeder extends Seeder
{
    public function run(): void
    {
        // Delete existing player user if exists
        User::where('email', 'player@example.com')->delete();

        // Create new user
        $user = User::create([
            'firstname' => 'Player',
            'lastname' => 'One',
            'username' => 'player1',
            'email' => 'player@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        // Create company
        $company = Company::create([
            'user_id' => $user->id,
            'funds' => 100000,
            'research_level' => 0,
            'carbon_footprint' => 0,
        ]);

        $this->command->info('User created successfully!');
        $this->command->info('User ID: ' . $user->id);
        $this->command->info('Company ID: ' . $company->id);
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('Email: player@example.com');
        $this->command->info('Username: player1');
        $this->command->info('Password: password');
    }
}

