<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure morph map for polymorphic relationships
        Relation::morphMap([
            'user' => 'App\\Models\\User',
            'product' => 'App\\Models\\Product',
            'product_recipe' => 'App\\Models\\ProductRecipe',
            'product_demand' => 'App\\Models\\ProductDemand',
            'file' => 'App\\Models\\File',
            'machine' => 'App\\Models\\Machine',
            'machine_output' => 'App\\Models\\MachineOutput',
            'country' => 'App\\Models\\Country',
            'employee_profile' => 'App\\Models\\EmployeeProfile',
            'machine_employee_profile' => 'App\\Models\\MachineEmployeeProfile',
            'technology' => 'App\\Models\\Technology',
            'setting' => 'App\\Models\\Setting',
            'supplier' => 'App\\Models\\Supplier',
            'purchase' => 'App\\Models\\Purchase',
            'inventory_movement' => 'App\\Models\\InventoryMovement',
            'transaction' => 'App\\Models\\Transaction',
            'bank' => 'App\\Models\\Bank',
            'loan' => 'App\\Models\\Loan',
            'advertiser' => 'App\\Models\\Advertiser',
            'ad' => 'App\\Models\\Ad',
            // Add more models as needed
        ]);
    }
}
