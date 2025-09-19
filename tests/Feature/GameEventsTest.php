<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Country;
use App\Models\Wilaya;
use App\Models\Supplier;
use App\Console\Commands\Events\CloseSuezCanal;
use App\Console\Commands\Events\BlockCountriesImport;
use App\Console\Commands\Events\AllowCountriesImport;
use App\Console\Commands\Events\RaiseOilPrice;
use App\Console\Commands\Events\LowerOilPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GameEventsTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $company;
    protected $country;
    protected $wilaya;
    protected $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create();
        
        $this->country = Country::factory()->create([
            'name' => 'Egypt',
            'allows_imports' => true,
            'customs_duties_rate' => 0.1,
        ]);
        
        $this->wilaya = Wilaya::factory()->create([
            'name' => 'Cairo',
            'min_shipping_cost' => 100,
            'max_shipping_cost' => 200,
        ]);
        
        $this->supplier = Supplier::factory()->create([
            'country_id' => $this->country->id,
            'wilaya_id' => $this->wilaya->id,
            'min_shipping_cost' => 50,
            'max_shipping_cost' => 150,
        ]);
        
        $this->company = Company::factory()->create([
            'funds' => 100000,
        ]);
    }

    /** @test */
    public function suez_canal_closure_affects_shipping_costs()
    {
        $event = new CloseSuezCanal();
        $event->handle();
        
        // Verify event is active
        $this->assertTrue($event->isActive());
        
        // Check that shipping costs increased for affected countries
        $this->country->refresh();
        $this->assertGreaterThan(1.0, $this->country->shipping_cost_multiplier);
        
        // Check that supplier costs increased
        $this->supplier->refresh();
        $this->assertGreaterThan(50, $this->supplier->real_shipping_cost);
    }

    /** @test */
    public function suez_canal_closure_can_be_reversed()
    {
        $event = new CloseSuezCanal();
        $event->handle();
        
        // Verify event is active
        $this->assertTrue($event->isActive());
        
        // Reopen Suez Canal
        $event->reverse();
        
        $this->country->refresh();
        $this->assertEquals(1.0, $this->country->shipping_cost_multiplier);
    }

    /** @test */
    public function blocked_country_imports_affect_supplier_availability()
    {
        $event = new BlockCountriesImport();
        $event->handle();
        
        $this->country->refresh();
        $this->assertFalse($this->country->allows_imports);
        
        // Check that supplier is no longer available for imports
        $this->supplier->refresh();
        $this->assertFalse($this->supplier->is_available);
    }

    /** @test */
    public function allowed_country_imports_restore_supplier_availability()
    {
        // First block the country
        $this->country->update(['allows_imports' => false]);
        
        $event = new AllowCountriesImport();
        $event->handle();
        
        $this->country->refresh();
        $this->assertTrue($this->country->allows_imports);
        
        // Check that supplier is available again
        $this->supplier->refresh();
        $this->assertTrue($this->supplier->is_available);
    }

    /** @test */
    public function oil_price_changes_affect_shipping_costs()
    {
        // Test oil price increase
        $raiseEvent = new RaiseOilPrice();
        $raiseEvent->handle();
        
        $this->supplier->refresh();
        $this->wilaya->refresh();
        
        // Shipping costs should have increased
        $this->assertGreaterThan(50, $this->supplier->min_shipping_cost);
        $this->assertGreaterThan(100, $this->wilaya->min_shipping_cost);
        
        // Test oil price decrease
        $lowerEvent = new LowerOilPrice();
        $lowerEvent->handle();
        
        $this->supplier->refresh();
        $this->wilaya->refresh();
        
        // Shipping costs should have decreased
        $this->assertLessThan($this->supplier->min_shipping_cost, $this->supplier->min_shipping_cost);
        $this->assertLessThan($this->wilaya->min_shipping_cost, $this->wilaya->min_shipping_cost);
    }

    /** @test */
    public function multiple_events_can_be_active_simultaneously()
    {
        // Run multiple events
        $suezEvent = new CloseSuezCanal();
        $suezEvent->handle();
        
        $blockEvent = new BlockCountriesImport();
        $blockEvent->handle();
        
        $oilEvent = new RaiseOilPrice();
        $oilEvent->handle();
        
        // Verify all events are active
        $this->assertTrue($suezEvent->isActive());
        $this->assertTrue($blockEvent->isActive());
        $this->assertTrue($oilEvent->isActive());
        
        // Verify combined effects
        $this->country->refresh();
        $this->supplier->refresh();
        $this->wilaya->refresh();
        
        $this->assertFalse($this->country->allows_imports);
        $this->assertGreaterThan(1.0, $this->country->shipping_cost_multiplier);
        $this->assertGreaterThan(50, $this->supplier->min_shipping_cost);
    }

    /** @test */
    public function events_affect_company_financial_decisions()
    {
        // Normal conditions
        $normalShippingCost = $this->supplier->min_shipping_cost;
        
        // Close Suez Canal
        $suezEvent = new CloseSuezCanal();
        $suezEvent->handle();
        
        $this->supplier->refresh();
        $increasedShippingCost = $this->supplier->min_shipping_cost;
        
        // Shipping costs should increase
        $this->assertGreaterThan($normalShippingCost, $increasedShippingCost);
        
        // Company should consider alternative suppliers or routes
        $alternativeSuppliers = Supplier::where('country_id', '!=', $this->country->id)
            ->where('is_available', true)
            ->get();
        
        $this->assertNotEmpty($alternativeSuppliers);
    }

    /** @test */
    public function events_have_duration_and_expiration()
    {
        // Test that events can expire automatically
        
        $event = new CloseSuezCanal();
        $event->handle();
        
        // Set event to expire in the past
        $event->setExpiresAt(now()->subDay());
        
        // Event should no longer be active
        $this->assertFalse($event->isActive());
        
        // Effects should be reversed
        $this->country->refresh();
        $this->assertEquals(1.0, $this->country->shipping_cost_multiplier);
    }

    /** @test */
    public function events_cascade_to_affect_company_operations()
    {
        // Test that events cascade to affect company operations
        
        // Close Suez Canal
        $suezEvent = new CloseSuezCanal();
        $suezEvent->handle();
        
        // Block imports from Egypt
        $blockEvent = new BlockCountriesImport();
        $blockEvent->handle();
        
        // Raise oil prices
        $oilEvent = new RaiseOilPrice();
        $oilEvent->handle();
        
        // Verify cascading effects
        $this->country->refresh();
        $this->supplier->refresh();
        $this->wilaya->refresh();
        
        $this->assertFalse($this->country->allows_imports);
        $this->assertGreaterThan(1.0, $this->country->shipping_cost_multiplier);
        $this->assertGreaterThan(50, $this->supplier->min_shipping_cost);
        $this->assertGreaterThan(100, $this->wilaya->min_shipping_cost);
        
        // Check that company can't import from blocked country
        $this->assertFalse($this->supplier->is_available);
    }

    /** @test */
    public function events_affect_procurement_decisions()
    {
        // Test that events influence procurement strategy
        
        // Normal conditions
        $normalSupplierCost = $this->supplier->min_shipping_cost;
        
        // Apply multiple events
        $suezEvent = new CloseSuezCanal();
        $suezEvent->handle();
        
        $oilEvent = new RaiseOilPrice();
        $oilEvent->handle();
        
        $this->supplier->refresh();
        $increasedSupplierCost = $this->supplier->min_shipping_cost;
        
        // Costs should have increased significantly
        $this->assertGreaterThan($normalSupplierCost * 1.5, $increasedSupplierCost);
        
        // Company should consider local suppliers or different routes
        $localSuppliers = Supplier::where('country_id', '!=', $this->country->id)
            ->where('is_available', true)
            ->get();
        
        $this->assertNotEmpty($localSuppliers);
    }

    /** @test */
    public function events_affect_market_dynamics()
    {
        // Test that events create market opportunities and challenges
        
        // Block imports from major supplier country
        $blockEvent = new BlockCountriesImport();
        $blockEvent->handle();
        
        // This should create opportunities for other suppliers
        $otherCountries = Country::where('id', '!=', $this->country->id)
            ->where('allows_imports', true)
            ->get();
        
        $this->assertNotEmpty($otherCountries);
        
        // Check that alternative suppliers become more attractive
        foreach ($otherCountries as $otherCountry) {
            $alternativeSuppliers = Supplier::where('country_id', $otherCountry->id)
                ->where('is_available', true)
                ->get();
            
            if ($alternativeSuppliers->isNotEmpty()) {
                $this->assertTrue(true); // Alternative suppliers exist
                break;
            }
        }
    }
}
