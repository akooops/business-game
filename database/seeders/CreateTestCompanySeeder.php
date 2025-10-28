<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateTestCompanySeeder extends Seeder
{
    public function run(): void
    {
        // Create new user
        $user = User::create([
            'firstname' => 'Test',
            'lastname' => 'Company',
            'username' => 'testcompany',
            'email' => 'testcompany@example.com',
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

        $this->command->info('✅ Test company created successfully!');
        $this->command->info('');
        $this->command->info('📋 Login credentials:');
        $this->command->info('Email: testcompany@example.com');
        $this->command->info('Username: testcompany');
        $this->command->info('Password: password');
        $this->command->info('');
        $this->command->info('User ID: ' . $user->id);
        $this->command->info('Company ID: ' . $company->id);
    }
}

