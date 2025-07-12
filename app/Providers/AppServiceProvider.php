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
            'role' => 'App\\Models\\Role',
            'permission' => 'App\\Models\\Permission',
            'user_role' => 'App\\Models\\UserRole',
            'role_permission' => 'App\\Models\\RolePermission',
            'machine' => 'App\\Models\\Machine',
            'machine_output' => 'App\\Models\\MachineOutput',
            // Add more models as needed
        ]);

        Blade::if('haspermission', function ($permission) {
            if (!config('app.enable_permissions')) {
                return true;
            }
            
            $user = auth()->user();
            if (!$user) {
                return false;
            }
            
            return $user->hasPermission($permission);
        });
    }
}
