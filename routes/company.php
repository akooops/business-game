<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Company\DashboardController;
use App\Http\Controllers\Company\TechnologiesController;
use App\Http\Controllers\Company\ProductsController;
use App\Http\Controllers\Company\SuppliersController;
use App\Http\Controllers\Company\WilayasController;
use App\Http\Controllers\Company\CountriesController;
use App\Http\Controllers\Company\PurchasesController;
use App\Http\Controllers\Company\ProductDemandController;
use App\Http\Controllers\Company\InventoryController;
use App\Http\Controllers\Company\SalesController;
use App\Http\Controllers\Company\EmployeeProfilesController;
use App\Http\Controllers\Company\EmployeesController;
use App\Http\Controllers\Company\MachinesController;
use App\Http\Controllers\Company\ProductionOrdersController;

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

Route::prefix('company')->middleware(['auth', 'check.company', 'handle.inertia'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('company.dashboard.index');

    // Countries
    Route::get('/countries', [CountriesController::class, 'index'])->name('company.countries.index');

    // Employee Profiles
    Route::get('/employee-profiles', [EmployeeProfilesController::class, 'index'])->name('company.employee-profiles.index');
    Route::get('/employee-profiles/{employeeProfile}/find-employees', [EmployeeProfilesController::class, 'findEmployees'])->name('company.employee-profiles.find-employees');

    // Employees
    Route::get('/employees', [EmployeesController::class, 'index'])->name('company.employees.index');
    Route::get('/employees/recruit-page', [EmployeesController::class, 'recruitPage'])->name('company.employees.recruit-page');
    Route::post('/employees/{employee}/fire', [EmployeesController::class, 'fire'])->name('company.employees.fire');
    Route::post('/employees/{employee}/promote', [EmployeesController::class, 'promote'])->name('company.employees.promote');
    Route::post('/employees/{employee}/recruit', [EmployeesController::class, 'recruit'])->name('company.employees.recruit');

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('company.inventory.index');

    // Machines
    Route::get('/machines', [MachinesController::class, 'index'])->name('company.machines.index');
    Route::get('/machines/setup-page', [MachinesController::class, 'setupPage'])->name('company.machines.setup-page');
    Route::post('/machines/{machine}/setup', [MachinesController::class, 'setup'])->name('company.machines.setup');
    Route::post('/machines/{companyMachine}/assign-employee', [MachinesController::class, 'assignEmployee'])->name('company.machines.assign-employee');
    Route::post('/machines/{companyMachine}/unassign-employee', [MachinesController::class, 'unassignEmployee'])->name('company.machines.unassign-employee');
    Route::post('/machines/{companyMachine}/start-maintenance', [MachinesController::class, 'startMaintenance'])->name('company.machines.start-maintenance');

    // Product Demand
    Route::get('/product-demand', [ProductDemandController::class, 'index'])->name('company.product-demand.index');

    // Products
    Route::get('/products', [ProductsController::class, 'index'])->name('company.products.index');
    Route::post('/products/{product}/fix-sale-price', [ProductsController::class, 'fixProductSalePrice'])->name('company.products.fix-sale-price');

    // Production Orders
    Route::post('/production-orders/{companyMachine}/produce', [ProductionOrdersController::class, 'produce'])->name('company.production-orders.store');

    // Purchases
    Route::get('/purchases', [PurchasesController::class, 'index'])->name('company.purchases.index');
    Route::get('/purchases/purchase-page', [PurchasesController::class, 'purchasePage'])->name('company.purchases.purchase-page');
    Route::post('/purchases', [PurchasesController::class, 'purchase'])->name('company.purchases.store');

    // Sales
    Route::get('/sales', [SalesController::class, 'index'])->name('company.sales.index');
    Route::post('/sales/{sale}/confirm', [SalesController::class, 'confirm'])->name('company.sales.store');

    // Suppliers
    Route::get('/suppliers', [SuppliersController::class, 'index'])->name('company.suppliers.index');

    // Technologies
    Route::get('/technologies', [TechnologiesController::class, 'index'])->name('company.technologies.index');
    Route::post('/technologies/{technology}/research', [TechnologiesController::class, 'research'])->name('company.technologies.research');

    // Wilayas
    Route::get('/wilayas', [WilayasController::class, 'index'])->name('company.wilayas.index');
});


