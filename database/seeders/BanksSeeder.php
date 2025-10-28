<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BanksSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            [
                'name' => 'Algerian National Bank',
                'loan_interest_rate' => 0.12,
                'loan_max_amount' => 100000,
                'loan_duration_months' => 24,
            ],
            [
                'name' => 'International Commerce Bank',
                'loan_interest_rate' => 0.15,
                'loan_max_amount' => 200000,
                'loan_duration_months' => 36,
            ],
            [
                'name' => 'Business Development Bank',
                'loan_interest_rate' => 0.09,
                'loan_max_amount' => 50000,
                'loan_duration_months' => 18,
            ],
        ];

        foreach ($banks as $data) {
            Bank::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
