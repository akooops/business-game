<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Core system data
            InitAdminAccountSeeder::class,
            SettingsSeeder::class,
            
            // Game world data
            CountriesSeeder::class,
            WilayasSeeder::class,
            BanksSeeder::class,
            EmployeeProfilesSeeder::class,
            AdvertisersSeeder::class,
            
            // Technology and products
            TechnologiesSeeder::class,
            ProductsSeeder::class,
            ProductRecipesSeeder::class,
            
            // Suppliers and machines
            SuppliersSeeder::class,
            SupplierProductsSeeder::class,
            MachinesSeeder::class,
            
            // Test companies and users
            CompaniesSeeder::class,
            CompaniesBootstrapSeeder::class,
        ]);
    }
}
