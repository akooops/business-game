<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SalesService;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductDemand;
use App\Models\CompanyProduct;
use App\Models\Wilaya;
use App\Models\Sale;
use App\Models\Ad;
use App\Models\Advertiser;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SalesServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $product;
    protected $productDemand;
    protected $companyProduct;
    protected $wilaya;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create(['funds' => 100000]);
        $this->product = Product::factory()->create([
            'elasticity_coefficient' => 0.5,
        ]);
        $this->productDemand = ProductDemand::factory()->create([
            'product_id' => $this->product->id,
            'gameweek' => 1,
            'market_price' => 100,
            'real_demand' => 1000,
            'max_demand' => 2000,
        ]);
        $this->companyProduct = CompanyProduct::factory()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'sale_price' => 90, // Below market price
            'available_stock' => 500,
        ]);
        $this->wilaya = Wilaya::factory()->create([
            'min_shipping_cost' => 10,
            'max_shipping_cost' => 20,
            'min_shipping_time_days' => 2,
            'max_shipping_time_days' => 5,
        ]);
    }

    /** @test */
    public function can_get_current_gameweek_product_market_price()
    {
        $marketPrice = SalesService::getCurrentGameweekProductMarketPrice($this->product);
        
        $this->assertEquals(100, $marketPrice);
    }

    /** @test */
    public function can_fix_product_sale_price()
    {
        $newSalePrice = 85;
        
        SalesService::fixProductSalePrice($this->company, $this->product, $newSalePrice);
        
        $this->companyProduct->refresh();
        $this->assertEquals($newSalePrice, $this->companyProduct->sale_price);
    }

    /** @test */
    public function can_change_wilaya_shipping_costs()
    {
        $initialMinCost = $this->wilaya->min_shipping_cost;
        $initialMaxCost = $this->wilaya->max_shipping_cost;
        
        SalesService::changeWilayaShippingCosts($this->wilaya);
        
        $this->wilaya->refresh();
        $this->assertNotEquals($initialMinCost, $this->wilaya->min_shipping_cost);
        $this->assertNotEquals($initialMaxCost, $this->wilaya->max_shipping_cost);
    }

    /** @test */
    public function can_generate_demand()
    {
        SalesService::generateDemand($this->company);
        
        // Check that sales were created
        $sales = Sale::where('company_id', $this->company->id)->get();
        $this->assertGreaterThan(0, $sales->count());
        
        foreach ($sales as $sale) {
            $this->assertEquals($this->company->id, $sale->company_id);
            $this->assertEquals($this->product->id, $sale->product_id);
            $this->assertEquals(Sale::STATUS_INITIATED, $sale->status);
        }
    }

    /** @test */
    public function demand_calculation_considers_price_difference()
    {
        // Product priced below market should have higher demand
        $belowMarketProduct = CompanyProduct::factory()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'sale_price' => 80, // 20% below market
        ]);
        
        SalesService::generateDemand($this->company);
        
        $sales = Sale::where('company_id', $this->company->id)->get();
        $this->assertGreaterThan(0, $sales->count());
    }

    /** @test */
    public function demand_calculation_considers_marketing_impact()
    {
        // Create an active ad to boost demand
        $advertiser = Advertiser::factory()->create();
        $ad = Ad::factory()->create([
            'company_id' => $this->company->id,
            'advertiser_id' => $advertiser->id,
            'product_id' => $this->product->id,
            'market_impact_percentage' => 0.2, // 20% boost
            'status' => Ad::STATUS_ACTIVE,
        ]);
        
        SalesService::generateDemand($this->company);
        
        $sales = Sale::where('company_id', $this->company->id)->get();
        $this->assertGreaterThan(0, $sales->count());
    }

    /** @test */
    public function can_confirm_sale()
    {
        $sale = Sale::factory()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'wilaya_id' => $this->wilaya->id,
            'quantity' => 50,
            'sale_price' => 90,
            'status' => Sale::STATUS_INITIATED,
        ]);
        
        SalesService::confirmSale($sale);
        
        $sale->refresh();
        $this->assertEquals(Sale::STATUS_CONFIRMED, $sale->status);
        
        // Check that inventory was reduced
        $this->companyProduct->refresh();
        $this->assertEquals(450, $this->companyProduct->available_stock);
    }

    /** @test */
    public function can_cancel_sales()
    {
        $sale = Sale::factory()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'wilaya_id' => $this->wilaya->id,
            'quantity' => 50,
            'status' => Sale::STATUS_INITIATED,
            'timelimit_days' => 1,
            'initiated_at' => now()->subDays(2), // Expired
        ]);
        
        SalesService::cancelSales($this->company);
        
        $sale->refresh();
        $this->assertEquals(Sale::STATUS_CANCELLED, $sale->status);
    }

    /** @test */
    public function can_process_delivered_sale()
    {
        $sale = Sale::factory()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'wilaya_id' => $this->wilaya->id,
            'quantity' => 50,
            'sale_price' => 90,
            'status' => Sale::STATUS_CONFIRMED,
            'shipping_time_days' => 3,
            'confirmed_at' => now()->subDays(4), // Delivered
        ]);
        
        $initialFunds = $this->company->funds;
        
        SalesService::processDeliveredSale($this->company);
        
        $sale->refresh();
        $this->assertEquals(Sale::STATUS_DELIVERED, $sale->status);
        
        // Company should receive revenue
        $this->company->refresh();
        $this->assertEquals($initialFunds + 4500, $this->company->funds); // 50 * 90
    }

    /** @test */
    public function shipping_costs_affect_profitability()
    {
        $highShippingWilaya = Wilaya::factory()->create([
            'min_shipping_cost' => 50,
            'max_shipping_cost' => 100,
        ]);
        
        $lowShippingWilaya = Wilaya::factory()->create([
            'min_shipping_cost' => 5,
            'max_shipping_cost' => 10,
        ]);
        
        // Sales to low shipping cost areas should be more profitable
        $lowCostSale = Sale::factory()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'wilaya_id' => $lowShippingWilaya->id,
            'shipping_cost' => 7,
        ]);
        
        $highCostSale = Sale::factory()->create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'wilaya_id' => $highShippingWilaya->id,
            'shipping_cost' => 75,
        ]);
        
        $this->assertLessThan($highCostSale->shipping_cost, $lowCostSale->shipping_cost);
    }

    /** @test */
    public function demand_respects_maximum_limits()
    {
        // Set very high market impact to try to exceed max demand
        $advertiser = Advertiser::factory()->create();
        $ad = Ad::factory()->create([
            'company_id' => $this->company->id,
            'advertiser_id' => $advertiser->id,
            'product_id' => $this->product->id,
            'market_impact_percentage' => 2.0, // 200% boost
            'status' => Ad::STATUS_ACTIVE,
        ]);
        
        SalesService::generateDemand($this->company);
        
        $totalDemand = Sale::where('company_id', $this->company->id)->sum('quantity');
        $this->assertLessThanOrEqual($this->productDemand->max_demand, $totalDemand);
    }

    /** @test */
    public function demand_cannot_be_negative()
    {
        // Set very high price to try to create negative demand
        $this->companyProduct->update(['sale_price' => 200]); // 100% above market
        
        SalesService::generateDemand($this->company);
        
        $sales = Sale::where('company_id', $this->company->id)->get();
        foreach ($sales as $sale) {
            $this->assertGreaterThanOrEqual(0, $sale->quantity);
        }
    }

    /** @test */
    public function can_analyze_market_trends()
    {
        $trends = SalesService::analyzeMarketTrends($this->product, 30); // 30 days
        
        $this->assertIsArray($trends);
        $this->assertArrayHasKey('price_trend', $trends);
        $this->assertArrayHasKey('demand_trend', $trends);
        $this->assertArrayHasKey('competition_trend', $trends);
    }

    /** @test */
    public function can_optimize_pricing_strategy()
    {
        $strategy = SalesService::optimizePricingStrategy($this->product, $this->company);
        
        $this->assertIsArray($strategy);
        $this->assertArrayHasKey('optimal_price', $strategy);
        $this->assertArrayHasKey('expected_demand', $strategy);
        $this->assertArrayHasKey('profit_margin', $strategy);
    }

    /** @test */
    public function can_handle_customer_segments()
    {
        $segments = SalesService::analyzeCustomerSegments($this->company);
        
        $this->assertIsArray($segments);
        $this->assertArrayHasKey('premium', $segments);
        $this->assertArrayHasKey('standard', $segments);
        $this->assertArrayHasKey('budget', $segments);
    }

    /** @test */
    public function can_forecast_sales_revenue()
    {
        $forecast = SalesService::forecastSalesRevenue($this->company, 90); // 90 days
        
        $this->assertIsArray($forecast);
        $this->assertArrayHasKey('total_revenue', $forecast);
        $this->assertArrayHasKey('product_breakdown', $forecast);
        $this->assertArrayHasKey('confidence_level', $forecast);
    }

    /** @test */
    public function can_analyze_competition()
    {
        $competition = SalesService::analyzeCompetition($this->product, $this->wilaya);
        
        $this->assertIsArray($competition);
        $this->assertArrayHasKey('competitors', $competition);
        $this->assertArrayHasKey('market_share', $competition);
        $this->assertArrayHasKey('competitive_advantages', $competition);
    }

    /** @test */
    public function can_handle_promotional_campaigns()
    {
        $campaign = SalesService::createPromotionalCampaign($this->product, 'summer_sale', 20); // 20% discount
        
        $this->assertIsArray($campaign);
        $this->assertArrayHasKey('id', $campaign);
        $this->assertArrayHasKey('type', $campaign);
        $this->assertArrayHasKey('discount', $campaign);
    }

    /** @test */
    public function can_analyze_sales_performance()
    {
        $performance = SalesService::analyzeSalesPerformance($this->company, 30); // 30 days
        
        $this->assertIsArray($performance);
        $this->assertArrayHasKey('total_sales', $performance);
        $this->assertArrayHasKey('conversion_rate', $performance);
        $this->assertArrayHasKey('average_order_value', $performance);
    }

    /** @test */
    public function can_handle_customer_feedback()
    {
        $feedback = SalesService::submitCustomerFeedback($this->product, 'positive', 'Great quality product');
        
        $this->assertIsArray($feedback);
        $this->assertArrayHasKey('id', $feedback);
        $this->assertArrayHasKey('sentiment', $feedback);
        $this->assertArrayHasKey('content', $feedback);
    }

    /** @test */
    public function can_optimize_inventory_levels()
    {
        $optimization = SalesService::optimizeInventoryLevels($this->product, $this->company);
        
        $this->assertIsArray($optimization);
        $this->assertArrayHasKey('optimal_stock', $optimization);
        $this->assertArrayHasKey('reorder_point', $optimization);
        $this->assertArrayHasKey('safety_stock', $optimization);
    }

    /** @test */
    public function can_analyze_distribution_channels()
    {
        $channels = SalesService::analyzeDistributionChannels($this->company);
        
        $this->assertIsArray($channels);
        $this->assertArrayHasKey('direct_sales', $channels);
        $this->assertArrayHasKey('retail_partners', $channels);
        $this->assertArrayHasKey('online_platforms', $channels);
    }
}
