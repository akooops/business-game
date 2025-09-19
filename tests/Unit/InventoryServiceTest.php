<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\InventoryService;
use App\Models\Company;
use App\Models\Product;
use App\Models\CompanyProduct;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\ProductionOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create([
            'funds' => 100000,
        ]);
        
        $this->product = Product::factory()->create();
    }

    /** @test */
    public function can_add_inventory_from_purchase()
    {
        $purchase = Purchase::factory()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'quantity' => 100,
        ]);

        $result = InventoryService::addInventoryFromPurchase($purchase);
        
        $this->assertTrue($result);
        
        $companyProduct = CompanyProduct::where('company_id', $this->company->id)
            ->where('product_id', $this->product->id)
            ->first();
            
        $this->assertEquals(100, $companyProduct->available_stock);
    }

    /** @test */
    public function can_remove_inventory_for_sale()
    {
        // First add inventory
        CompanyProduct::factory()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'available_stock' => 200,
        ]);

        $sale = Sale::factory()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);

        $result = InventoryService::removeInventoryForSale($sale);
        
        $this->assertTrue($result);
        
        $companyProduct = CompanyProduct::where('company_id', $this->company->id)
            ->where('product_id', $this->product->id)
            ->first();
            
        $this->assertEquals(150, $companyProduct->available_stock);
    }

    /** @test */
    public function can_handle_production_inventory()
    {
        $companyProduct = CompanyProduct::factory()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'available_stock' => 100,
        ]);

        $productionOrder = ProductionOrder::factory()->create([
            'company_machine_id' => \App\Models\CompanyMachine::factory()->create([
                'company_id' => $this->company->id,
            ]),
            'product_id' => $this->product->id,
            'quantity' => 25,
        ]);

        $result = InventoryService::productionStarted($productionOrder, $this->product, 25);
        
        $this->assertTrue($result);
        
        $companyProduct->refresh();
        $this->assertEquals(75, $companyProduct->available_stock);
    }

    /** @test */
    public function can_validate_inventory_levels()
    {
        $companyProduct = CompanyProduct::factory()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'available_stock' => 50,
        ]);

        $isSufficient = InventoryService::validateInventoryLevels($this->company, $this->product, 30);
        $this->assertTrue($isSufficient);

        $isInsufficient = InventoryService::validateInventoryLevels($this->company, $this->product, 100);
        $this->assertFalse($isInsufficient);
    }

    /** @test */
    public function can_calculate_reorder_points()
    {
        $reorderPoint = InventoryService::calculateReorderPoint($this->product, $this->company);
        
        $this->assertIsNumeric($reorderPoint);
        $this->assertGreaterThan(0, $reorderPoint);
    }

    /** @test */
    public function can_analyze_inventory_turnover()
    {
        $turnover = InventoryService::analyzeInventoryTurnover($this->company, 30); // 30 days
        
        $this->assertIsArray($turnover);
        $this->assertArrayHasKey('average_turnover', $turnover);
        $this->assertArrayHasKey('slow_moving_items', $turnover);
        $this->assertArrayHasKey('fast_moving_items', $turnover);
    }

    /** @test */
    public function can_handle_inventory_adjustments()
    {
        $companyProduct = CompanyProduct::factory()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'available_stock' => 100,
        ]);

        $result = InventoryService::adjustInventory($this->company, $this->product, 20, 'damaged');
        
        $this->assertTrue($result);
        
        $companyProduct->refresh();
        $this->assertEquals(80, $companyProduct->available_stock);
    }

    /** @test */
    public function can_forecast_inventory_needs()
    {
        $forecast = InventoryService::forecastInventoryNeeds($this->company, 90); // 90 days
        
        $this->assertIsArray($forecast);
        $this->assertArrayHasKey('total_requirements', $forecast);
        $this->assertArrayHasKey('product_breakdown', $forecast);
        $this->assertArrayHasKey('purchase_recommendations', $forecast);
    }

    /** @test */
    public function can_optimize_inventory_distribution()
    {
        $optimization = InventoryService::optimizeInventoryDistribution($this->company);
        
        $this->assertIsArray($optimization);
        $this->assertArrayHasKey('central_warehouse', $optimization);
        $this->assertArrayHasKey('regional_distribution', $optimization);
        $this->assertArrayHasKey('cost_savings', $optimization);
    }

    /** @test */
    public function can_handle_inventory_audits()
    {
        $audit = InventoryService::conductInventoryAudit($this->company);
        
        $this->assertIsArray($audit);
        $this->assertArrayHasKey('total_items', $audit);
        $this->assertArrayHasKey('discrepancies', $audit);
        $this->assertArrayHasKey('accuracy_percentage', $audit);
    }

    /** @test */
    public function can_manage_supplier_inventory()
    {
        $supplierInventory = InventoryService::getSupplierInventory($this->company);
        
        $this->assertIsArray($supplierInventory);
        $this->assertArrayHasKey('available_suppliers', $supplierInventory);
        $this->assertArrayHasKey('lead_times', $supplierInventory);
        $this->assertArrayHasKey('cost_comparison', $supplierInventory);
    }
}
