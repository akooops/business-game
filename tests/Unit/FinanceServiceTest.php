<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FinanceService;
use App\Models\Company;
use App\Models\Bank;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\Machine;
use App\Models\Technology;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Ad;
use App\Models\InventoryMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $bank;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create([
            'funds' => 100000,
        ]);
    }

    /** @test */
    public function can_check_sufficient_funds()
    {
        $this->assertTrue(FinanceService::haveSufficientFunds($this->company, 50000));
        $this->assertFalse(FinanceService::haveSufficientFunds($this->company, 150000));
    }

    /** @test */
    public function can_pay_technology_research()
    {
        $technology = Technology::factory()->create(['research_cost' => 10000]);
        $initialFunds = $this->company->funds;
        
        $remainingFunds = FinanceService::payTechnologyResearch($this->company, $technology);
        
        $this->company->refresh();
        $this->assertEquals($initialFunds - 10000, $this->company->funds);
        $this->assertEquals($remainingFunds, $this->company->funds);
        
        // Check transaction was created
        $this->assertDatabaseHas('transactions', [
            'company_id' => $this->company->id,
            'amount' => 10000,
            'type' => Transaction::TYPE_TECHNOLOGY,
        ]);
    }

    /** @test */
    public function can_pay_purchase()
    {
        $purchase = Purchase::factory()->create([
            'total_cost' => 15000,
            'company_id' => $this->company->id,
        ]);
        
        $initialFunds = $this->company->funds;
        $remainingFunds = FinanceService::payPurchase($this->company, $purchase);
        
        $this->company->refresh();
        $this->assertEquals($initialFunds - 15000, $this->company->funds);
        $this->assertEquals($remainingFunds, $this->company->funds);
        
        // Check transaction was created
        $this->assertDatabaseHas('transactions', [
            'company_id' => $this->company->id,
            'amount' => 15000,
            'type' => 'purchase',
        ]);
    }

    /** @test */
    public function can_pay_inventory_costs()
    {
        $product = Product::factory()->create();
        $totalCost = 8000;
        
        $initialFunds = $this->company->funds;
        $remainingFunds = FinanceService::payInventoryCosts($this->company, $product, $totalCost);
        
        $this->company->refresh();
        $this->assertEquals($initialFunds - 8000, $this->company->funds);
        $this->assertEquals($remainingFunds, $this->company->funds);
        
        // Check transaction was created
        $this->assertDatabaseHas('transactions', [
            'company_id' => $this->company->id,
            'amount' => 8000,
            'type' => 'inventory',
        ]);
    }

    /** @test */
    public function can_pay_machine_setup_cost()
    {
        $machine = Machine::factory()->create(['cost_to_acquire' => 25000]);
        
        $initialFunds = $this->company->funds;
        FinanceService::payMachineSetupCost($this->company, $machine);
        
        $this->company->refresh();
        $this->assertEquals($initialFunds - 25000, $this->company->funds);
        
        // Check transaction was created
        $this->assertDatabaseHas('transactions', [
            'company_id' => $this->company->id,
            'amount' => 25000,
            'type' => 'machine_setup',
        ]);
    }

    /** @test */
    public function can_pay_machine_operation_cost()
    {
        $machine = Machine::factory()->create([
            'operations_cost' => 5000,
        ]);
        
        $initialFunds = $this->company->funds;
        FinanceService::payMachineOperationCost($this->company, $machine);
        
        $this->company->refresh();
        $this->assertEquals($initialFunds - 5000, $this->company->funds);
        
        // Check transaction was created
        $this->assertDatabaseHas('transactions', [
            'company_id' => $this->company->id,
            'amount' => 5000,
            'type' => 'machine_operations',
        ]);
    }

    /** @test */
    public function can_pay_employee_salary()
    {
        $salary = 3000;
        
        $initialFunds = $this->company->funds;
        FinanceService::payEmployeesSalary($this->company, $salary);
        
        $this->company->refresh();
        $this->assertEquals($initialFunds - 3000, $this->company->funds);
        
        // Check transaction was created
        $this->assertDatabaseHas('transactions', [
            'company_id' => $this->company->id,
            'amount' => 3000,
            'type' => 'employee_salary',
        ]);
    }

    /** @test */
    public function can_pay_ad_package()
    {
        $ad = Ad::factory()->create([
            'price' => 5000,
        ]);
        
        $initialFunds = $this->company->funds;
        FinanceService::payAdPackage($this->company, $ad);
        
        $this->company->refresh();
        $this->assertEquals($initialFunds - 5000, $this->company->funds);
        
        // Check transaction was created
        $this->assertDatabaseHas('transactions', [
            'company_id' => $this->company->id,
            'amount' => 5000,
            'type' => 'marketing',
        ]);
    }

    /** @test */
    public function can_receive_loan()
    {
        $loanAmount = 50000;
        $loanId = 123;
        
        $initialFunds = $this->company->funds;
        FinanceService::receiveLoan($this->company, $loanAmount, $loanId);
        
        $this->company->refresh();
        $this->assertEquals($initialFunds + 50000, $this->company->funds);
        
        // Check transaction was created
        $this->assertDatabaseHas('transactions', [
            'company_id' => $this->company->id,
            'amount' => 50000,
            'type' => 'loan_received',
        ]);
    }

    /** @test */
    public function can_receive_sale_revenue()
    {
        $sale = Sale::factory()->create([
            'company_id' => $this->company->id,
            'sale_price' => 12000,
            'quantity' => 1,
        ]);
        
        $expectedRevenue = $sale->sale_price * $sale->quantity;
        $initialFunds = $this->company->funds;
        FinanceService::receiveSalePayment($this->company, $sale);
        
        $this->company->refresh();
        $this->assertEquals($initialFunds + $expectedRevenue, $this->company->funds);
        
        // Check transaction was created
        $this->assertDatabaseHas('transactions', [
            'company_id' => $this->company->id,
            'amount' => $expectedRevenue,
            'type' => 'sale_payment',
        ]);
    }

    /** @test */
    public function can_pay_maintenance_cost()
    {
        $maintenance = (object) ['maintenances_cost' => 3000];
        
        $initialFunds = $this->company->funds;
        FinanceService::payMaintenanceCost($this->company, $maintenance);
        
        $this->company->refresh();
        $this->assertEquals($initialFunds - 3000, $this->company->funds);
        
        // Check transaction was created
        $this->assertDatabaseHas('transactions', [
            'company_id' => $this->company->id,
            'amount' => 3000,
            'type' => 'maintenance',
        ]);
    }

    /** @test */
    public function can_pay_loan_payment()
    {
        $loanPayment = 2500;
        
        $initialFunds = $this->company->funds;
        FinanceService::payLoan($this->company, $loanPayment);
        
        $this->company->refresh();
        $this->assertEquals($initialFunds - 2500, $this->company->funds);
        
        // Check transaction was created
        $this->assertDatabaseHas('transactions', [
            'company_id' => $this->company->id,
            'amount' => 2500,
            'type' => 'loan_payment',
        ]);
    }

    /** @test */
    public function can_handle_insufficient_funds()
    {
        $this->company->update(['funds' => 1000]); // Low funds
        
        $purchase = Purchase::factory()->create([
            'total_cost' => 5000, // More than available funds
            'company_id' => $this->company->id,
        ]);

        $this->expectException(\Exception::class);
        FinanceService::payPurchase($this->company, $purchase);
    }

    /** @test */
    public function can_calculate_company_valuation()
    {
        $valuation = FinanceService::calculateCompanyValuation($this->company);
        
        $this->assertIsNumeric($valuation);
        $this->assertGreaterThan(0, $valuation);
    }

    /** @test */
    public function can_analyze_cash_flow()
    {
        $cashFlow = FinanceService::analyzeCashFlow($this->company, 30); // 30 days
        
        $this->assertIsArray($cashFlow);
        $this->assertArrayHasKey('inflow', $cashFlow);
        $this->assertArrayHasKey('outflow', $cashFlow);
        $this->assertArrayHasKey('net', $cashFlow);
    }

    /** @test */
    public function can_forecast_financial_position()
    {
        $forecast = FinanceService::forecastFinancialPosition($this->company, 90); // 90 days
        
        $this->assertIsArray($forecast);
        $this->assertArrayHasKey('projected_funds', $forecast);
        $this->assertArrayHasKey('projected_revenue', $forecast);
        $this->assertArrayHasKey('projected_expenses', $forecast);
    }

    /** @test */
    public function can_validate_financial_transactions()
    {
        $transaction = Transaction::factory()->create([
            'company_id' => $this->company->id,
            'amount' => 1000,
            'type' => 'test',
        ]);

        $isValid = FinanceService::validateTransaction($transaction);
        
        $this->assertIsBool($isValid);
    }

    /** @test */
    public function can_generate_financial_reports()
    {
        $report = FinanceService::generateFinancialReport($this->company, 'monthly');
        
        $this->assertIsArray($report);
        $this->assertArrayHasKey('period', $report);
        $this->assertArrayHasKey('revenue', $report);
        $this->assertArrayHasKey('expenses', $report);
        $this->assertArrayHasKey('profit', $report);
    }

    /** @test */
    public function can_handle_currency_conversions()
    {
        $amount = 1000;
        $convertedAmount = FinanceService::convertCurrency($amount, 'USD', 'EUR');
        
        $this->assertIsNumeric($convertedAmount);
        $this->assertGreaterThan(0, $convertedAmount);
    }

    /** @test */
    public function can_calculate_tax_obligations()
    {
        $taxObligation = FinanceService::calculateTaxObligation($this->company, 2024);
        
        $this->assertIsNumeric($taxObligation);
        $this->assertGreaterThanOrEqual(0, $taxObligation);
    }

    /** @test */
    public function can_manage_investment_portfolio()
    {
        $portfolio = FinanceService::getInvestmentPortfolio($this->company);
        
        $this->assertIsArray($portfolio);
        $this->assertArrayHasKey('total_value', $portfolio);
        $this->assertArrayHasKey('investments', $portfolio);
    }

    /** @test */
    public function can_analyze_profit_margins()
    {
        $profitMargin = FinanceService::calculateProfitMargin($this->company, 30); // 30 days
        
        $this->assertIsNumeric($profitMargin);
        $this->assertGreaterThanOrEqual(-100, $profitMargin); // Can be negative
        $this->assertLessThanOrEqual(100, $profitMargin);
    }
}
