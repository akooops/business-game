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

Route::get('/', function () {
    return view('welcome');
});

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
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::patch('notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::patch('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    Route::get('utilities', [UtilitiesController::class, 'index'])->name('utilities.index');
});

Route::prefix('admin')->middleware(['auth', 'check.admin', 'handle.inertia'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard.index');

    // Companies
    Route::get('companies', [CompaniesController::class, 'index'])->name('admin.companies.index');
    Route::get('companies/create', [CompaniesController::class, 'create'])->name('admin.companies.create');
    Route::post('companies', [CompaniesController::class, 'store'])->name('admin.companies.store');
    Route::get('companies/{company}', [CompaniesController::class, 'show'])->name('admin.companies.show');
    Route::get('companies/{company}/edit', [CompaniesController::class, 'edit'])->name('admin.companies.edit');
    Route::patch('companies/{company}', [CompaniesController::class, 'update'])->name('admin.companies.update');
    Route::delete('companies/{company}', [CompaniesController::class, 'destroy'])->name('admin.companies.destroy');

    // Countries
    Route::get('countries', [CountriesController::class, 'index'])->name('admin.countries.index');
    Route::get('countries/create', [CountriesController::class, 'create'])->name('admin.countries.create');
    Route::post('countries', [CountriesController::class, 'store'])->name('admin.countries.store');
    Route::get('countries/{country}', [CountriesController::class, 'show'])->name('admin.countries.show');
    Route::get('countries/{country}/edit', [CountriesController::class, 'edit'])->name('admin.countries.edit');
    Route::patch('countries/{country}', [CountriesController::class, 'update'])->name('admin.countries.update');
    Route::delete('countries/{country}', [CountriesController::class, 'destroy'])->name('admin.countries.destroy');

    // Employee Profiles
    Route::get('employee-profiles', [EmployeeProfilesController::class, 'index'])->name('admin.employee-profiles.index');
    Route::get('employee-profiles/create', [EmployeeProfilesController::class, 'create'])->name('admin.employee-profiles.create');
    Route::post('employee-profiles', [EmployeeProfilesController::class, 'store'])->name('admin.employee-profiles.store');
    Route::get('employee-profiles/{employeeProfile}', [EmployeeProfilesController::class, 'show'])->name('admin.employee-profiles.show');
    Route::get('employee-profiles/{employeeProfile}/edit', [EmployeeProfilesController::class, 'edit'])->name('admin.employee-profiles.edit');
    Route::patch('employee-profiles/{employeeProfile}', [EmployeeProfilesController::class, 'update'])->name('admin.employee-profiles.update');
    Route::delete('employee-profiles/{employeeProfile}', [EmployeeProfilesController::class, 'destroy'])->name('admin.employee-profiles.destroy');

    // Machines 
    Route::get('machines', [MachinesController::class, 'index'])->name('admin.machines.index');
    Route::get('machines/create', [MachinesController::class, 'create'])->name('admin.machines.create');
    Route::post('machines', [MachinesController::class, 'store'])->name('admin.machines.store');
    Route::get('machines/{machine}', [MachinesController::class, 'show'])->name('admin.machines.show');
    Route::get('machines/{machine}/edit', [MachinesController::class, 'edit'])->name('admin.machines.edit');
    Route::patch('machines/{machine}', [MachinesController::class, 'update'])->name('admin.machines.update');
    Route::delete('machines/{machine}', [MachinesController::class, 'destroy'])->name('admin.machines.destroy');

    // Product Demand
    Route::get('product-demand', [ProductDemandController::class, 'index'])->name('admin.product-demand.index');
    Route::post('product-demand', [ProductDemandController::class, 'store'])->name('admin.product-demand.store');
    Route::patch('product-demand/{productDemand}', [ProductDemandController::class, 'update'])->name('admin.product-demand.update');
    Route::delete('product-demand/{productDemand}', [ProductDemandController::class, 'destroy'])->name('admin.product-demand.destroy');

    // Product Recipe
    Route::get('product-recipes', [ProductRecipesController::class, 'index'])->name('admin.product-recipes.index');
    Route::post('product-recipes', [ProductRecipesController::class, 'store'])->name('admin.product-recipes.store');
    Route::patch('product-recipes/{productRecipe}', [ProductRecipesController::class, 'update'])->name('admin.product-recipes.update');
    Route::delete('product-recipes/{productRecipe}', [ProductRecipesController::class, 'destroy'])->name('admin.product-recipes.destroy');

    // Products
    Route::get('products', [ProductsController::class, 'index'])->name('admin.products.index');
    Route::get('products/create', [ProductsController::class, 'create'])->name('admin.products.create');
    Route::post('products', [ProductsController::class, 'store'])->name('admin.products.store');
    Route::get('products/{product}', [ProductsController::class, 'show'])->name('admin.products.show');
    Route::get('products/{product}/edit', [ProductsController::class, 'edit'])->name('admin.products.edit');
    Route::patch('products/{product}', [ProductsController::class, 'update'])->name('admin.products.update');
    Route::delete('products/{product}', [ProductsController::class, 'destroy'])->name('admin.products.destroy');

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::patch('settings/{setting}', [SettingsController::class, 'update'])->name('admin.settings.update');

    // Suppliers
    Route::get('suppliers', [SuppliersController::class, 'index'])->name('admin.suppliers.index');
    Route::get('suppliers/create', [SuppliersController::class, 'create'])->name('admin.suppliers.create');
    Route::post('suppliers', [SuppliersController::class, 'store'])->name('admin.suppliers.store');
    Route::get('suppliers/{supplier}', [SuppliersController::class, 'show'])->name('admin.suppliers.show');
    Route::get('suppliers/{supplier}/edit', [SuppliersController::class, 'edit'])->name('admin.suppliers.edit');
    Route::patch('suppliers/{supplier}', [SuppliersController::class, 'update'])->name('admin.suppliers.update');
    Route::delete('suppliers/{supplier}', [SuppliersController::class, 'destroy'])->name('admin.suppliers.destroy');

    // Technologies
    Route::get('technologies', [TechnologiesController::class, 'index'])->name('admin.technologies.index');
    Route::get('technologies/create', [TechnologiesController::class, 'create'])->name('admin.technologies.create');
    Route::post('technologies', [TechnologiesController::class, 'store'])->name('admin.technologies.store');
    Route::get('technologies/{technology}', [TechnologiesController::class, 'show'])->name('admin.technologies.show');
    Route::get('technologies/{technology}/edit', [TechnologiesController::class, 'edit'])->name('admin.technologies.edit');
    Route::patch('technologies/{technology}', [TechnologiesController::class, 'update'])->name('admin.technologies.update');
    Route::delete('technologies/{technology}', [TechnologiesController::class, 'destroy'])->name('admin.technologies.destroy');

    // Users
    Route::get('users', [UsersController::class, 'index'])->name('admin.users.index');
    Route::get('users/create', [UsersController::class, 'create'])->name('admin.users.create');
    Route::post('users', [UsersController::class, 'store'])->name('admin.users.store');
    Route::get('users/{user}', [UsersController::class, 'show'])->name('admin.users.show');
    Route::get('users/{user}/edit', [UsersController::class, 'edit'])->name('admin.users.edit');
    Route::patch('users/{user}', [UsersController::class, 'update'])->name('admin.users.update');
    Route::delete('users/{user}', [UsersController::class, 'destroy'])->name('admin.users.destroy');

    // Wilayas
    Route::get('wilayas', [WilayasController::class, 'index'])->name('admin.wilayas.index');
    Route::get('wilayas/create', [WilayasController::class, 'create'])->name('admin.wilayas.create');
    Route::post('wilayas', [WilayasController::class, 'store'])->name('admin.wilayas.store');
    Route::get('wilayas/{wilaya}', [WilayasController::class, 'show'])->name('admin.wilayas.show');
    Route::get('wilayas/{wilaya}/edit', [WilayasController::class, 'edit'])->name('admin.wilayas.edit');
    Route::patch('wilayas/{wilaya}', [WilayasController::class, 'update'])->name('admin.wilayas.update');
    Route::delete('wilayas/{wilaya}', [WilayasController::class, 'destroy'])->name('admin.wilayas.destroy');
});

include 'company.php';




