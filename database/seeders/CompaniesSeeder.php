<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompaniesSeeder extends Seeder
{
    public function run(): void
    {
        // Create a non-admin user with a company if none exists
        $user = User::firstWhere('email', 'player@example.com');
        if (!$user) {
            $user = User::create([
                'firstname' => 'Player',
                'lastname' => 'One',
                'username' => 'player1',
                'email' => 'player@example.com',
                'email_verified_at' => now(),
                'password' => 'password',
            ]);
        }

        Company::updateOrCreate(
            ['user_id' => $user->id],
            [
                'funds' => 100000,
                'research_level' => 0,
                'carbon_footprint' => 0,
            ]
        );
    }
}


