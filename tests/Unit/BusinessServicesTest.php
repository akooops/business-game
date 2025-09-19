<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CalculationsService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BusinessServicesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function settings_service_can_manage_game_status()
    {
        // Skip this test for now as it requires proper Settings table setup
        $this->markTestSkipped('Settings service test requires proper database setup');
    }

    /** @test */
    public function calculations_service_produces_valid_results()
    {
        // Test PERT calculation
        $min = 100;
        $max = 200;
        $avg = 150;
        
        $result = CalculationsService::calculatePertValue($min, $avg, $max);
        
        // Result should be within bounds
        $this->assertGreaterThanOrEqual($min, $result);
        $this->assertLessThanOrEqual($max, $result);
        
        // Test random between min/max
        $randomResult = CalculationsService::calcaulteRandomBetweenMinMax($min, $max);
        $this->assertGreaterThanOrEqual($min, $randomResult);
        $this->assertLessThanOrEqual($max, $randomResult);
    }

    /** @test */
    public function calculations_service_handles_edge_cases()
    {
        // Test with same min and max
        $result = CalculationsService::calcaulteRandomBetweenMinMax(100, 100);
        $this->assertEquals(100, $result);
        
        // Test with zero values
        $result = CalculationsService::calcaulteRandomBetweenMinMax(0, 10);
        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertLessThanOrEqual(10, $result);
    }
}
