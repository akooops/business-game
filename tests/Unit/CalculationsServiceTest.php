<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\CalculationsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CalculationsServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pert_distribution_calculation_works()
    {
        $min = 100;
        $max = 200;
        $avg = 150;
        
        $result = CalculationsService::calculatePertValue($min, $avg, $max);
        
        // Result should be within bounds
        $this->assertGreaterThanOrEqual($min, $result);
        $this->assertLessThanOrEqual($max, $result);
        $this->assertIsNumeric($result);
    }

    /** @test */
    public function random_between_min_max_works()
    {
        $min = 50;
        $max = 100;
        
        $result = CalculationsService::calcaulteRandomBetweenMinMax($min, $max);
        
        // Result should be within bounds
        $this->assertGreaterThanOrEqual($min, $result);
        $this->assertLessThanOrEqual($max, $result);
        $this->assertIsNumeric($result);
    }

    /** @test */
    public function random_between_handles_edge_cases()
    {
        // Same min and max
        $result = CalculationsService::calcaulteRandomBetweenMinMax(100, 100);
        $this->assertEquals(100, $result);
        
        // Zero values
        $result = CalculationsService::calcaulteRandomBetweenMinMax(0, 10);
        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertLessThanOrEqual(10, $result);
        
        // Negative values
        $result = CalculationsService::calcaulteRandomBetweenMinMax(-10, 10);
        $this->assertGreaterThanOrEqual(-10, $result);
        $this->assertLessThanOrEqual(10, $result);
    }

    /** @test */
    public function pert_calculation_produces_consistent_results()
    {
        $min = 100;
        $max = 200;
        $avg = 150;
        
        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $results[] = CalculationsService::calculatePertValue($min, $avg, $max);
        }
        
        // All results should be within bounds
        foreach ($results as $result) {
            $this->assertGreaterThanOrEqual($min, $result);
            $this->assertLessThanOrEqual($max, $result);
        }
        
        // Results should vary (not all the same)
        $this->assertGreaterThan(1, count(array_unique($results)));
    }
}
