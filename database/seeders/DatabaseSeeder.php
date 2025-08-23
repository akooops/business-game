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
            InitAdminAccountSeeder::class,
            SettingsSeeder::class,
            CountriesSeeder::class,
            WilayasSeeder::class,
            TechnologiesSeeder::class,
            ProductsSeeder::class,
            ProductRecipesSeeder::class,
            SuppliersSeeder::class,
            SupplierProductsSeeder::class,
            EmployeeProfilesSeeder::class,
            MachinesSeeder::class,
            MachineOutputsSeeder::class,
            CompaniesSeeder::class,
            CompaniesBootstrapSeeder::class,
        ]);
    }
}
