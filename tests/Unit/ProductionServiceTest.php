<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ProductionService;
use App\Models\Company;
use App\Models\Machine;
use App\Models\CompanyMachine;
use App\Models\Employee;
use App\Models\ProductionOrder;
use App\Models\Product;
use App\Models\CompanyProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $machine;
    protected $employee;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create(['funds' => 100000]);
        $this->machine = Machine::factory()->create([
            'min_speed' => 10,
            'max_speed' => 20,
            'min_maintenance_cost' => 100,
            'max_maintenance_cost' => 200,
            'min_maintenance_time_days' => 1,
            'max_maintenance_time_days' => 3,
        ]);
        $this->employee = Employee::factory()->create(['company_id' => $this->company->id]);
        $this->product = Product::factory()->create();
    }

    /** @test */
    public function can_setup_machine()
    {
        $companyMachine = ProductionService::setupMachine($this->company, $this->machine);
        
        $this->assertInstanceOf(CompanyMachine::class, $companyMachine);
        $this->assertEquals($this->company->id, $companyMachine->company_id);
        $this->assertEquals($this->machine->id, $companyMachine->machine_id);
        $this->assertEquals('inactive', $companyMachine->status);
        $this->assertGreaterThanOrEqual($this->machine->min_speed, $companyMachine->speed);
        $this->assertLessThanOrEqual($this->machine->max_speed, $companyMachine->speed);
    }

    /** @test */
    public function can_assign_employee_to_machine()
    {
        $companyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $this->machine->id,
        ]);
        
        ProductionService::assignEmployee($companyMachine, $this->employee);
        
        $this->assertEquals($this->employee->id, $companyMachine->employee_id);
    }

    /** @test */
    public function can_start_production()
    {
        $companyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $this->machine->id,
            'employee_id' => $this->employee->id,
            'status' => 'inactive',
        ]);
        
        $companyProduct = CompanyProduct::factory()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'available_stock' => 100,
        ]);
        
        $quantity = 50;
        $productionOrder = ProductionService::startProduction($companyMachine, $this->product, $quantity);
        
        $this->assertInstanceOf(ProductionOrder::class, $productionOrder);
        $this->assertEquals($companyMachine->id, $productionOrder->company_machine_id);
        $this->assertEquals($this->product->id, $productionOrder->product_id);
        $this->assertEquals($quantity, $productionOrder->quantity);
        $this->assertEquals('in_progress', $productionOrder->status);
    }

    /** @test */
    public function can_complete_production()
    {
        $companyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $this->machine->id,
            'employee_id' => $this->employee->id,
        ]);
        
        $productionOrder = ProductionOrder::factory()->create([
            'company_machine_id' => $companyMachine->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'status' => 'in_progress',
        ]);
        
        ProductionService::completeProduction($this->company);
        
        $productionOrder->refresh();
        $this->assertEquals('completed', $productionOrder->status);
        
        // Check that inventory was increased
        $companyProduct = CompanyProduct::where('company_id', $this->company->id)
            ->where('product_id', $this->product->id)
            ->first();
        $this->assertEquals(50, $companyProduct->available_stock);
    }

    /** @test */
    public function can_pay_machine_operation_cost()
    {
        $companyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $this->machine->id,
            'operations_cost' => 1000,
        ]);
        
        $initialFunds = $this->company->funds;
        
        ProductionService::payMachineOperationCost($this->company);
        
        $this->company->refresh();
        $this->assertEquals($initialFunds - 1000, $this->company->funds);
    }

    /** @test */
    public function can_calculate_machines_value()
    {
        $companyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $this->machine->id,
            'current_value' => 10000,
            'loss_on_sale_days' => 0.001, // 0.1% per day
            'setup_at' => now()->subDays(10),
        ]);
        
        ProductionService::calculateMachinesValue($this->company);
        
        $companyMachine->refresh();
        // Value should have decreased due to time passing
        $this->assertLessThan(10000, $companyMachine->current_value);
    }

    /** @test */
    public function can_sell_machine()
    {
        $companyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $this->machine->id,
            'current_value' => 8000,
        ]);
        
        $initialFunds = $this->company->funds;
        
        ProductionService::sellMachine($companyMachine);
        
        $this->company->refresh();
        $this->assertEquals($initialFunds + 8000, $this->company->funds);
        
        // Machine should be marked as sold
        $this->assertEquals('sold', $companyMachine->status);
    }

    /** @test */
    public function production_quality_depends_on_machine_and_employee()
    {
        $highQualityMachine = Machine::factory()->create(['quality_factor' => 0.9]);
        $highEfficiencyEmployee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'efficiency_factor' => 0.9,
        ]);
        
        $lowQualityMachine = Machine::factory()->create(['quality_factor' => 0.5]);
        $lowEfficiencyEmployee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'efficiency_factor' => 0.5,
        ]);
        
        $highQualityCompanyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $highQualityMachine->id,
            'employee_id' => $highEfficiencyEmployee->id,
        ]);
        
        $lowQualityCompanyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $lowQualityMachine->id,
            'employee_id' => $lowEfficiencyEmployee->id,
        ]);
        
        // High quality setup should produce better results
        $this->assertGreaterThan(
            $lowQualityCompanyMachine->quality_factor,
            $highQualityCompanyMachine->quality_factor
        );
    }

    /** @test */
    public function production_speed_depends_on_machine_and_employee()
    {
        $fastMachine = Machine::factory()->create([
            'min_speed' => 20,
            'max_speed' => 30,
        ]);
        $fastEmployee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'efficiency_factor' => 0.9,
        ]);
        
        $slowMachine = Machine::factory()->create([
            'min_speed' => 5,
            'max_speed' => 10,
        ]);
        $slowEmployee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'efficiency_factor' => 0.5,
        ]);
        
        $fastCompanyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $fastMachine->id,
            'employee_id' => $fastEmployee->id,
        ]);
        
        $slowCompanyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $slowMachine->id,
            'employee_id' => $slowEmployee->id,
        ]);
        
        // Fast setup should have higher speed
        $this->assertGreaterThan(
            $slowCompanyMachine->speed,
            $fastCompanyMachine->speed
        );
    }

    /** @test */
    public function can_calculate_production_efficiency()
    {
        $companyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $this->machine->id,
            'quality_factor' => 0.8,
            'speed' => 15,
        ]);

        $employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'efficiency_factor' => 0.9,
        ]);

        ProductionService::assignEmployee($companyMachine, $employee);

        $efficiency = ProductionService::calculateProductionEfficiency($companyMachine);
        
        $this->assertGreaterThan(0, $efficiency);
        $this->assertLessThanOrEqual(1, $efficiency);
    }

    /** @test */
    public function can_handle_production_errors()
    {
        $companyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $this->machine->id,
            'status' => 'broken',
        ]);

        $this->expectException(\Exception::class);
        ProductionService::startProduction($companyMachine, $this->product, 50);
    }

    /** @test */
    public function can_optimize_production_schedule()
    {
        $companyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $this->machine->id,
            'speed' => 20,
        ]);

        $schedule = ProductionService::optimizeProductionSchedule($companyMachine, $this->product, 100);
        
        $this->assertIsArray($schedule);
        $this->assertArrayHasKey('estimated_time', $schedule);
        $this->assertArrayHasKey('optimal_quantity', $schedule);
    }

    /** @test */
    public function can_handle_machine_maintenance()
    {
        $companyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $this->machine->id,
            'current_reliability' => 0.3, // Low reliability
        ]);

        $maintenanceResult = ProductionService::scheduleMaintenance($companyMachine);
        
        $this->assertTrue($maintenanceResult);
        $this->assertEquals('maintenance', $companyMachine->fresh()->status);
    }

    /** @test */
    public function can_calculate_production_costs()
    {
        $companyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $this->machine->id,
            'operations_cost' => 1000,
            'maintenance_cost' => 500,
        ]);

        $costs = ProductionService::calculateProductionCosts($companyMachine, 100);
        
        $this->assertIsArray($costs);
        $this->assertArrayHasKey('operations', $costs);
        $this->assertArrayHasKey('maintenance', $costs);
        $this->assertArrayHasKey('total', $costs);
    }

    /** @test */
    public function can_validate_production_requirements()
    {
        $companyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $this->machine->id,
        ]);

        $requirements = ProductionService::getProductionRequirements($this->product, 50);
        
        $this->assertIsArray($requirements);
        $this->assertArrayHasKey('materials', $requirements);
        $this->assertArrayHasKey('time', $requirements);
        $this->assertArrayHasKey('costs', $requirements);
    }
}
