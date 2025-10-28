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
            EmployeeProfilesSeeder::class,
            TechnologiesSeeder::class,
            ProductsSeeder::class,
            ProductRecipesSeeder::class,
            MachinesSeeder::class,
            MachineOutputsSeeder::class,
            SuppliersSeeder::class,
            SupplierProductsSeeder::class,
            AdvertisersSeeder::class,
            ProductDemandsSeeder::class,
            CompaniesSeeder::class,
            CompaniesBootstrapSeeder::class,
            InventoryMovementsSeeder::class,
        ]);
    }
}
