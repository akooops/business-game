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
    Route::get('/', [DashboardController::class, 'index'])->middleware('check.permission:company.dashboard.index')->name('company.dashboard.index');

    Route::get('/technologies', [TechnologiesController::class, 'index'])->middleware('check.permission:company.technologies.index')->name('company.technologies.index');
    Route::get('/technologies/research', [TechnologiesController::class, 'researchPage'])->middleware('check.permission:company.technologies.index')->name('company.technologies.research-page');
    Route::post('/technologies/{technology}/research', [TechnologiesController::class, 'research'])->middleware('check.permission:company.technologies.research')->name('company.technologies.research');

    Route::get('/products', [ProductsController::class, 'index'])->middleware('check.permission:company.products.index')->name('company.products.index');
    Route::post('/products/{product}/fix-sale-price', [ProductsController::class, 'fixProductSalePrice'])->middleware('check.permission:company.products.index')->name('company.products.fix-sale-price');
    
    Route::get('/product-demand', [ProductDemandController::class, 'index'])->middleware('check.permission:company.product-demand.index')->name('company.product-demand.index');

    Route::get('/wilayas', [WilayasController::class, 'index'])->middleware('check.permission:company.wilayas.index')->name('company.wilayas.index');
    Route::get('/countries', [CountriesController::class, 'index'])->middleware('check.permission:company.countries.index')->name('company.countries.index');

    Route::get('/suppliers', [SuppliersController::class, 'index'])->middleware('check.permission:company.suppliers.index')->name('company.suppliers.index');

    Route::get('/purchases', [PurchasesController::class, 'index'])->middleware('check.permission:company.purchases.index')->name('company.purchases.index');
    Route::post('/purchases/{product}/purchase', [PurchasesController::class, 'purchase'])->middleware('check.permission:company.purchases.store')->name('company.purchases.store');

    Route::get('/purchases/purchase-page', [PurchasesController::class, 'purchasePage'])->middleware('check.permission:company.purchases.index')->name('company.purchases.purchase-page');

    Route::get('/inventory', [InventoryController::class, 'index'])->middleware('check.permission:company.inventory.index')->name('company.inventory.index');

    Route::get('/sales', [SalesController::class, 'index'])->middleware('check.permission:company.sales.index')->name('company.sales.index');
    Route::post('/sales/{sale}/confirm', [SalesController::class, 'confirm'])->middleware('check.permission:company.sales.store')->name('company.sales.store');


    Route::get('/employee-profiles', [EmployeeProfilesController::class, 'index'])->middleware('check.permission:company.employee-profiles.index')->name('company.employee-profiles.index');
    Route::get('/employee-profiles/{employeeProfile}/find-employees', [EmployeeProfilesController::class, 'findEmployees'])->middleware('check.permission:company.employee-profiles.index')->name('company.employee-profiles.find-employees');


    Route::get('/employees', [EmployeesController::class, 'index'])->middleware('check.permission:company.employees.index')->name('company.employees.index');
    Route::get('/employees/recruit-page', [EmployeesController::class, 'recruitPage'])->middleware('check.permission:company.employees.index')->name('company.employees.recruit-page');
    Route::post('/employees/{employee}/recruit', [EmployeesController::class, 'recruit'])->middleware('check.permission:company.employees.store')->name('company.employees.store');
    Route::post('/employees/{employee}/promote', [EmployeesController::class, 'promote'])->middleware('check.permission:company.employees.store')->name('company.employees.promote');
    Route::post('/employees/{employee}/fire', [EmployeesController::class, 'fire'])->middleware('check.permission:company.employees.store')->name('company.employees.fire');

    Route::get('/machines', [MachinesController::class, 'index'])->middleware('check.permission:company.machines.index')->name('company.machines.index');
    Route::get('/machines/setup-page', [MachinesController::class, 'setupPage'])->middleware('check.permission:company.machines.index')->name('company.machines.setup-page');
    Route::post('/machines/{machine}/setup', [MachinesController::class, 'setup'])->middleware('check.permission:company.machines.store')->name('company.machines.setup');

    Route::post('/machines/{companyMachine}/assign-employee', [MachinesController::class, 'assignEmployee'])->middleware('check.permission:company.machines.index')->name('company.machines.assign-employee');
    Route::post('/machines/{companyMachine}/unassign-employee', [MachinesController::class, 'unassignEmployee'])->middleware('check.permission:company.machines.index')->name('company.machines.unassign-employee');
    Route::post('/machines/{companyMachine}/start-maintenance', [MachinesController::class, 'startMaintenance'])->middleware('check.permission:company.machines.index')->name('company.machines.start-maintenance');

    Route::post('/production-orders/{companyMachine}/produce', [ProductionOrdersController::class, 'produce'])->middleware('check.permission:company.production-orders.store')->name('company.production-orders.store');
});


