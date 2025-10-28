<?php

use Illuminate\Support\Facades\Route;
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
use App\Http\Controllers\Company\TransactionsController;
use App\Http\Controllers\Company\BanksController;
use App\Http\Controllers\Company\LoansController;
use App\Http\Controllers\Company\AdvertisersController;
use App\Http\Controllers\Company\AdsController;
use App\Http\Controllers\Company\DashboardController;

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

Route::prefix('company')->middleware(['auth', 'check.company', 'handle.inertia'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('company.dashboard.index');
    Route::get('/dashboard/current', [DashboardController::class, 'current'])->name('company.dashboard.current');
    Route::get('/dashboard/historical', [DashboardController::class, 'historical'])->name('company.dashboard.historical');

    // Banks
    Route::get('/banks', [BanksController::class, 'index'])->name('company.banks.index');

    // Loans
    Route::get('/loans', [LoansController::class, 'index'])->name('company.loans.index');

    // Advertisers
    Route::get('/advertisers', [AdvertisersController::class, 'index'])->name('company.advertisers.index');

    // Ads
    Route::get('/ads', [AdsController::class, 'index'])->name('company.ads.index');

    // Countries
    Route::get('/countries', [CountriesController::class, 'index'])->name('company.countries.index');

    // Employee Profiles
    Route::get('/employee-profiles', [EmployeeProfilesController::class, 'index'])->name('company.employee-profiles.index');
    Route::get('/employee-profiles/{employeeProfile}/find-employees', [EmployeeProfilesController::class, 'findEmployees'])->name('company.employee-profiles.find-employees');

    // Employees
    Route::get('/employees', [EmployeesController::class, 'index'])->name('company.employees.index');
    Route::get('/employees/recruit-page', [EmployeesController::class, 'recruitPage'])->name('company.employees.recruit-page');

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('company.inventory.index');

    // Machines
    Route::get('/machines', [MachinesController::class, 'index'])->name('company.machines.index');
    Route::get('/machines/setup-page', [MachinesController::class, 'setupPage'])->name('company.machines.setup-page');
    
    // Product Demand
    Route::get('/product-demand', [ProductDemandController::class, 'index'])->name('company.product-demand.index');

    // Products
    Route::get('/products', [ProductsController::class, 'index'])->name('company.products.index');

    // Production Orders
    Route::get('/production-orders', [ProductionOrdersController::class, 'index'])->name('company.production-orders.index');

    // Purchases
    Route::get('/purchases', [PurchasesController::class, 'index'])->name('company.purchases.index');
    Route::get('/purchases/purchase-page', [PurchasesController::class, 'purchasePage'])->name('company.purchases.purchase-page');

    // Sales
    Route::get('/sales', [SalesController::class, 'index'])->name('company.sales.index');

    // Suppliers
    Route::get('/suppliers', [SuppliersController::class, 'index'])->name('company.suppliers.index');

    // Technologies
    Route::get('/technologies', [TechnologiesController::class, 'index'])->name('company.technologies.index');

    // Transactions
    Route::get('/transactions', [TransactionsController::class, 'index'])->name('company.transactions.index');

    // Wilayas
    Route::get('/wilayas', [WilayasController::class, 'index'])->name('company.wilayas.index');

    // ========================================================================
    // FINANCIAL OPERATIONS - Protected by throttle.company middleware
    // These operations modify company funds and must be serialized to prevent
    // race conditions. Maximum 1 operation at a time per company.
    // ========================================================================
    Route::middleware(['throttle.company:5,0.5'])->group(function () {
        // Loans - Borrow and pay money
        Route::post('/loans', [LoansController::class, 'store'])->name('company.loans.store');
        Route::post('/loans/{loan}/pay', [LoansController::class, 'pay'])->name('company.loans.pay');

        // Ads - Purchase advertising packages
        Route::post('/ads', [AdsController::class, 'store'])->name('company.ads.store');

        // Employees - Recruitment and promotions (affect funds/salaries)
        Route::post('/employees/{employee}/fire', [EmployeesController::class, 'fire'])->name('company.employees.fire');
        Route::post('/employees/{employee}/promote', [EmployeesController::class, 'promote'])->name('company.employees.promote');
        Route::post('/employees/{employee}/recruit', [EmployeesController::class, 'recruit'])->name('company.employees.recruit');

        // Machines - Purchase, sell, and maintain machines
        Route::post('/machines/{machine}/setup', [MachinesController::class, 'setup'])->name('company.machines.setup');
        Route::post('/machines/{companyMachine}/sell', [MachinesController::class, 'sell'])->name('company.machines.sell');
        Route::post('/machines/{companyMachine}/assign-employee', [MachinesController::class, 'assignEmployee'])->name('company.machines.assign-employee');
        Route::post('/machines/{companyMachine}/unassign-employee', [MachinesController::class, 'unassignEmployee'])->name('company.machines.unassign-employee');
        Route::post('/machines/{companyMachine}/start-maintenance', [MachinesController::class, 'startMaintenance'])->name('company.machines.start-maintenance');

        // Products - Fix sale prices (affects revenue)
        Route::post('/products/{product}/fix-sale-price', [ProductsController::class, 'fixProductSalePrice'])->name('company.products.fix-sale-price');

        // Production Orders - Start and cancel production
        Route::post('/production-orders/{companyMachine}/produce', [ProductionOrdersController::class, 'produce'])->name('company.production-orders.store');
        Route::post('/production-orders/{productionOrder}/cancel', [ProductionOrdersController::class, 'cancel'])->name('company.production-orders.cancel');

        // Purchases - Buy products from suppliers
        Route::post('/purchases', [PurchasesController::class, 'purchase'])->name('company.purchases.store');

        // Sales - Confirm and deliver sales
        Route::post('/sales/{sale}/confirm', [SalesController::class, 'confirm'])->name('company.sales.store');

        // Technologies - Research technologies
        Route::post('/technologies/{technology}/research', [TechnologiesController::class, 'research'])->name('company.technologies.research');
    });
});


