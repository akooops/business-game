<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyProduct;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Wilaya;
use Illuminate\Database\Seeder;

class PopulateTestCompanyDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get the test company
        $company = Company::whereHas('user', function($query) {
            $query->where('email', 'testcompany@example.com');
        })->first();

        if (!$company) {
            $this->command->error('Test company not found. Run CreateTestCompanySeeder first.');
            return;
        }

        // Get all products
        $products = Product::all();
        $wilayas = Wilaya::all();

        if ($products->isEmpty()) {
            $this->command->error('No products found. Run ProductsSeeder first.');
            return;
        }

        // 1. Add/Update company products with good stock and prices
        $this->command->info('Adding products to company inventory...');
        foreach ($products as $product) {
            CompanyProduct::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'product_id' => $product->id
                ],
                [
                    'available_stock' => rand(50, 200), // Good stock levels
                    'sale_price' => rand(200, 800), // Reasonable prices
                ]
            );
        }

        // 2. Create some sales
        $this->command->info('Creating sales records...');
        if ($wilayas->isNotEmpty()) {
            for ($i = 0; $i < 10; $i++) {
                $product = $products->random();
                $wilaya = $wilayas->random();
                $status = ['initiated', 'confirmed', 'delivered'][rand(0, 2)];
                
                $initiatedAt = now()->subDays(rand(0, 60));
                
                $saleData = [
                    'company_id' => $company->id,
                    'product_id' => $product->id,
                    'wilaya_id' => $wilaya->id,
                    'quantity' => rand(1, 50),
                    'sale_price' => rand(100, 500) + (rand(0, 999) / 1000),
                    'shipping_cost' => rand(10, 100) + (rand(0, 999) / 1000),
                    'shipping_time_days' => rand(1, 7),
                    'status' => $status,
                    'initiated_at' => $initiatedAt,
                    'created_at' => $initiatedAt,
                    'updated_at' => now(),
                ];
                
                if ($status !== 'initiated') {
                    $saleData['confirmed_at'] = $initiatedAt->copy()->addDays(rand(1, 5));
                }
                
                if ($status === 'delivered') {
                    $saleData['delivered_at'] = $initiatedAt->copy()->addDays(rand(7, 21));
                }
                
                Sale::create($saleData);
            }
        }

        // 3. Create some employees
        $this->command->info('Creating employees...');
        $profiles = \App\Models\EmployeeProfile::all();
        
        if ($profiles->isNotEmpty()) {
            for ($i = 0; $i < 5; $i++) {
                $profile = $profiles->random();
                
                Employee::create([
                    'company_id' => $company->id,
                    'employee_profile_id' => $profile->id,
                    'name' => fake()->name(),
                    'salary_month' => rand(30000, 120000),
                    'recruitment_cost' => rand(1000, 5000),
                    'current_mood' => rand(50, 100),
                    'mood_decay_rate_days' => rand(1, 30),
                    'efficiency_factor' => rand(50, 100),
                    'status' => 'active',
                    'applied_at' => now()->subDays(rand(7, 30)),
                    'timelimit_days' => rand(1, 7),
                    'hired_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }

        $this->command->info('');
        $this->command->info('✅ Test company populated successfully!');
        $this->command->info('');
        $this->command->info('📊 Added data:');
        $this->command->info('   - ' . $products->count() . ' products in inventory');
        $this->command->info('   - 10 sales records');
        $this->command->info('   - 5 employees');
    }
}

