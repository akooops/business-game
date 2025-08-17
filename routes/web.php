<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UtilitiesController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\ProductDemandController;
use App\Http\Controllers\Admin\ProductRecipesController;
use App\Http\Controllers\Admin\EmployeeProfilesController;
use App\Http\Controllers\Admin\MachinesController;
use App\Http\Controllers\Admin\CountriesController;
use App\Http\Controllers\Admin\WilayasController;
use App\Http\Controllers\Admin\TechnologiesController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SuppliersController;
use App\Http\Controllers\Admin\CompaniesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', '/auth/login');

/*
|--------------------------------------------------------------------------
| Admin Authentication Routes
|--------------------------------------------------------------------------
*/



Route::middleware(['handle.inertia'])->group(function () {
    Route::get('auth/login', [AuthController::class, 'showLoginForm'])->middleware('guest')->name('auth.login');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('guest')->name('auth.login');
});

Route::middleware(['handle.inertia'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth')->name('auth.logout');
});

Route::middleware(['auth', 'handle.inertia'])->group(function () {
    Route::get('notifications', [NotificationController::class, 'index'])->middleware('check.permission:notifications.index')->name('notifications.index');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->middleware('check.permission:notifications.index')->name('notifications.unread-count');
    Route::patch('notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->middleware('check.permission:notifications.index')->name('notifications.mark-read');
    Route::patch('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->middleware('check.permission:notifications.index')->name('notifications.mark-all-read');

    Route::get('utilities/current-timestamp', [UtilitiesController::class, 'currentTimeStamp'])->name('utilities.current-timestamp');
});

Route::prefix('admin')->middleware(['auth', 'check.admin', 'handle.inertia'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->middleware('check.permission:admin.dashboard.index')->name('admin.dashboard.index');

    // Permissions
    Route::get('permissions', [PermissionsController::class, 'index'])->middleware('check.permission:admin.permissions.index')->name('admin.permissions.index');

    // Roles
    Route::get('roles', [RolesController::class, 'index'])->middleware('check.permission:admin.roles.index')->name('admin.roles.index');
    Route::get('roles/create', [RolesController::class, 'create'])->middleware('check.permission:admin.roles.store')->name('admin.roles.create');
    Route::post('roles', [RolesController::class, 'store'])->middleware('check.permission:admin.roles.store')->name('admin.roles.store');
    Route::get('roles/{role}', [RolesController::class, 'show'])->middleware('check.permission:admin.roles.show')->name('admin.roles.show');
    Route::get('roles/{role}/edit', [RolesController::class, 'edit'])->middleware('check.permission:admin.roles.update')->name('admin.roles.edit');
    Route::patch('roles/{role}', [RolesController::class, 'update'])->middleware('check.permission:admin.roles.update')->name('admin.roles.update');
    Route::delete('roles/{role}', [RolesController::class, 'destroy'])->middleware('check.permission:admin.roles.destroy')->name('admin.roles.destroy');

    // Users
    Route::get('users', [UsersController::class, 'index'])->middleware('check.permission:admin.users.index')->name('admin.users.index');
    Route::get('users/create', [UsersController::class, 'create'])->middleware('check.permission:admin.users.store')->name('admin.users.create');
    Route::post('users', [UsersController::class, 'store'])->middleware('check.permission:admin.users.store')->name('admin.users.store');
    Route::get('users/{user}', [UsersController::class, 'show'])->middleware('check.permission:admin.users.show')->name('admin.users.show');
    Route::get('users/{user}/edit', [UsersController::class, 'edit'])->middleware('check.permission:admin.users.update')->name('admin.users.edit');
    Route::patch('users/{user}', [UsersController::class, 'update'])->middleware('check.permission:admin.users.update')->name('admin.users.update');
    Route::delete('users/{user}', [UsersController::class, 'destroy'])->middleware('check.permission:admin.users.destroy')->name('admin.users.destroy');


    // Technologies
    Route::get('technologies', [TechnologiesController::class, 'index'])->middleware('check.permission:admin.technologies.index')->name('admin.technologies.index');
    Route::get('technologies/create', [TechnologiesController::class, 'create'])->middleware('check.permission:admin.technologies.store')->name('admin.technologies.create');
    Route::post('technologies', [TechnologiesController::class, 'store'])->middleware('check.permission:admin.technologies.store')->name('admin.technologies.store');
    Route::get('technologies/{technology}', [TechnologiesController::class, 'show'])->middleware('check.permission:admin.technologies.show')->name('admin.technologies.show');
    Route::get('technologies/{technology}/edit', [TechnologiesController::class, 'edit'])->middleware('check.permission:admin.technologies.update')->name('admin.technologies.edit');
    Route::patch('technologies/{technology}', [TechnologiesController::class, 'update'])->middleware('check.permission:admin.technologies.update')->name('admin.technologies.update');
    Route::delete('technologies/{technology}', [TechnologiesController::class, 'destroy'])->middleware('check.permission:admin.technologies.destroy')->name('admin.technologies.destroy');

    //Settings
    Route::get('settings', [SettingsController::class, 'index'])->middleware('check.permission:admin.settings.index')->name('admin.settings.index');
    Route::patch('settings/{setting}', [SettingsController::class, 'update'])->middleware('check.permission:admin.settings.update')->name('admin.settings.update');

    // Products
    Route::get('products', [ProductsController::class, 'index'])->middleware('check.permission:admin.products.index')->name('admin.products.index');
    Route::get('products/create', [ProductsController::class, 'create'])->middleware('check.permission:admin.products.store')->name('admin.products.create');
    Route::post('products', [ProductsController::class, 'store'])->middleware('check.permission:admin.products.store')->name('admin.products.store');
    Route::get('products/{product}', [ProductsController::class, 'show'])->middleware('check.permission:admin.products.show')->name('admin.products.show');
    Route::get('products/{product}/edit', [ProductsController::class, 'edit'])->middleware('check.permission:admin.products.update')->name('admin.products.edit');
    Route::patch('products/{product}', [ProductsController::class, 'update'])->middleware('check.permission:admin.products.update')->name('admin.products.update');
    Route::delete('products/{product}', [ProductsController::class, 'destroy'])->middleware('check.permission:admin.products.destroy')->name('admin.products.destroy');
    
    // Product Demand
    Route::get('product-demand', [ProductDemandController::class, 'index'])->middleware('check.permission:admin.product-demand.index')->name('admin.product-demand.index');
    Route::post('product-demand', [ProductDemandController::class, 'store'])->middleware('check.permission:admin.product-demand.store')->name('admin.product-demand.store');
    Route::patch('product-demand/{productDemand}', [ProductDemandController::class, 'update'])->middleware('check.permission:admin.product-demand.update')->name('admin.product-demand.update');
    Route::delete('product-demand/{productDemand}', [ProductDemandController::class, 'destroy'])->middleware('check.permission:admin.product-demand.destroy')->name('admin.product-demand.destroy');
    
    // Product Recipe
    Route::get('product-recipes', [ProductRecipesController::class, 'index'])->middleware('check.permission:admin.product-recipes.index')->name('admin.product-recipes.index');
    Route::post('product-recipes', [ProductRecipesController::class, 'store'])->middleware('check.permission:admin.product-recipes.store')->name('admin.product-recipes.store');
    Route::patch('product-recipes/{productRecipe}', [ProductRecipesController::class, 'update'])->middleware('check.permission:admin.product-recipes.update')->name('admin.product-recipes.update');
    Route::delete('product-recipes/{productRecipe}', [ProductRecipesController::class, 'destroy'])->middleware('check.permission:admin.product-recipes.destroy')->name('admin.product-recipes.destroy');

    // Employee Profiles
    Route::get('employee-profiles', [EmployeeProfilesController::class, 'index'])->middleware('check.permission:admin.employee-profiles.index')->name('admin.employee-profiles.index');
    Route::get('employee-profiles/create', [EmployeeProfilesController::class, 'create'])->middleware('check.permission:admin.employee-profiles.store')->name('admin.employee-profiles.create');
    Route::post('employee-profiles', [EmployeeProfilesController::class, 'store'])->middleware('check.permission:admin.employee-profiles.store')->name('admin.employee-profiles.store');
    Route::get('employee-profiles/{employeeProfile}', [EmployeeProfilesController::class, 'show'])->middleware('check.permission:admin.employee-profiles.show')->name('admin.employee-profiles.show');
    Route::get('employee-profiles/{employeeProfile}/edit', [EmployeeProfilesController::class, 'edit'])->middleware('check.permission:admin.employee-profiles.update')->name('admin.employee-profiles.edit');
    Route::patch('employee-profiles/{employeeProfile}', [EmployeeProfilesController::class, 'update'])->middleware('check.permission:admin.employee-profiles.update')->name('admin.employee-profiles.update');
    Route::delete('employee-profiles/{employeeProfile}', [EmployeeProfilesController::class, 'destroy'])->middleware('check.permission:admin.employee-profiles.destroy')->name('admin.employee-profiles.destroy');

    // Machines 
    Route::get('machines', [MachinesController::class, 'index'])->middleware('check.permission:admin.machines.index')->name('admin.machines.index');
    Route::get('machines/create', [MachinesController::class, 'create'])->middleware('check.permission:admin.machines.store')->name('admin.machines.create');
    Route::post('machines', [MachinesController::class, 'store'])->middleware('check.permission:admin.machines.store')->name('admin.machines.store');
    Route::get('machines/{machine}', [MachinesController::class, 'show'])->middleware('check.permission:admin.machines.show')->name('admin.machines.show');
    Route::get('machines/{machine}/edit', [MachinesController::class, 'edit'])->middleware('check.permission:admin.machines.update')->name('admin.machines.edit');
    Route::patch('machines/{machine}', [MachinesController::class, 'update'])->middleware('check.permission:admin.machines.update')->name('admin.machines.update');
    Route::delete('machines/{machine}', [MachinesController::class, 'destroy'])->middleware('check.permission:admin.machines.destroy')->name('admin.machines.destroy');

    // Countries
    Route::get('countries', [CountriesController::class, 'index'])->middleware('check.permission:admin.countries.index')->name('admin.countries.index');
    Route::get('countries/create', [CountriesController::class, 'create'])->middleware('check.permission:admin.countries.store')->name('admin.countries.create');
    Route::post('countries', [CountriesController::class, 'store'])->middleware('check.permission:admin.countries.store')->name('admin.countries.store');
    Route::get('countries/{country}', [CountriesController::class, 'show'])->middleware('check.permission:admin.countries.show')->name('admin.countries.show');
    Route::get('countries/{country}/edit', [CountriesController::class, 'edit'])->middleware('check.permission:admin.countries.update')->name('admin.countries.edit');
    Route::patch('countries/{country}', [CountriesController::class, 'update'])->middleware('check.permission:admin.countries.update')->name('admin.countries.update');
    Route::delete('countries/{country}', [CountriesController::class, 'destroy'])->middleware('check.permission:admin.countries.destroy')->name('admin.countries.destroy');

    // Wilayas
    Route::get('wilayas', [WilayasController::class, 'index'])->middleware('check.permission:admin.wilayas.index')->name('admin.wilayas.index');
    Route::get('wilayas/create', [WilayasController::class, 'create'])->middleware('check.permission:admin.wilayas.store')->name('admin.wilayas.create');
    Route::post('wilayas', [WilayasController::class, 'store'])->middleware('check.permission:admin.wilayas.store')->name('admin.wilayas.store');
    Route::get('wilayas/{wilaya}', [WilayasController::class, 'show'])->middleware('check.permission:admin.wilayas.show')->name('admin.wilayas.show');
    Route::get('wilayas/{wilaya}/edit', [WilayasController::class, 'edit'])->middleware('check.permission:admin.wilayas.update')->name('admin.wilayas.edit');
    Route::patch('wilayas/{wilaya}', [WilayasController::class, 'update'])->middleware('check.permission:admin.wilayas.update')->name('admin.wilayas.update');
    Route::delete('wilayas/{wilaya}', [WilayasController::class, 'destroy'])->middleware('check.permission:admin.wilayas.destroy')->name('admin.wilayas.destroy');

    // Suppliers
    Route::get('suppliers', [SuppliersController::class, 'index'])->middleware('check.permission:admin.suppliers.index')->name('admin.suppliers.index');
    Route::get('suppliers/create', [SuppliersController::class, 'create'])->middleware('check.permission:admin.suppliers.store')->name('admin.suppliers.create');
    Route::post('suppliers', [SuppliersController::class, 'store'])->middleware('check.permission:admin.suppliers.store')->name('admin.suppliers.store');
    Route::get('suppliers/{supplier}', [SuppliersController::class, 'show'])->middleware('check.permission:admin.suppliers.show')->name('admin.suppliers.show');
    Route::get('suppliers/{supplier}/edit', [SuppliersController::class, 'edit'])->middleware('check.permission:admin.suppliers.update')->name('admin.suppliers.edit');
    Route::patch('suppliers/{supplier}', [SuppliersController::class, 'update'])->middleware('check.permission:admin.suppliers.update')->name('admin.suppliers.update');
    Route::delete('suppliers/{supplier}', [SuppliersController::class, 'destroy'])->middleware('check.permission:admin.suppliers.destroy')->name('admin.suppliers.destroy');

    // Companies
    Route::get('companies', [CompaniesController::class, 'index'])->middleware('check.permission:admin.companies.index')->name('admin.companies.index');
    Route::get('companies/create', [CompaniesController::class, 'create'])->middleware('check.permission:admin.companies.store')->name('admin.companies.create');
    Route::post('companies', [CompaniesController::class, 'store'])->middleware('check.permission:admin.companies.store')->name('admin.companies.store');
    Route::get('companies/{company}', [CompaniesController::class, 'show'])->middleware('check.permission:admin.companies.show')->name('admin.companies.show');
    Route::get('companies/{company}/edit', [CompaniesController::class, 'edit'])->middleware('check.permission:admin.companies.update')->name('admin.companies.edit');
    Route::patch('companies/{company}', [CompaniesController::class, 'update'])->middleware('check.permission:admin.companies.update')->name('admin.companies.update');
    Route::delete('companies/{company}', [CompaniesController::class, 'destroy'])->middleware('check.permission:admin.companies.destroy')->name('admin.companies.destroy');
});

include 'company.php';




