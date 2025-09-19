<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\CalculationsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_endpoint_is_accessible()
    {
        $response = $this->get('/test');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Test endpoint working']);
    }

    /** @test */
    public function calculations_service_pert_distribution_works()
    {
        $min = 10;
        $max = 20;
        
        $result = CalculationsService::calculatePertValue($min, 15, $max);
        
        // Result should be between min and max
        $this->assertGreaterThanOrEqual($min, $result);
        $this->assertLessThanOrEqual($max, $result);
        
        // Result should be a number
        $this->assertIsNumeric($result);
    }

    /** @test */
    public function calculations_service_random_between_works()
    {
        $min = 5;
        $max = 15;
        
        $result = CalculationsService::calcaulteRandomBetweenMinMax($min, $max);
        
        // Result should be between min and max
        $this->assertGreaterThanOrEqual($min, $result);
        $this->assertLessThanOrEqual($max, $result);
        
        // Result should be a number
        $this->assertIsNumeric($result);
    }
}
