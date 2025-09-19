<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TechnolgiesResearchService;
use App\Models\Company;
use App\Models\Technology;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TechnologyServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $technology;
    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create([
            'funds' => 100000,
            'research_level' => 5,
        ]);
        
        $this->technology = Technology::factory()->create([
            'level' => 3,
            'research_cost' => 25000,
        ]);
        
        $this->employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'efficiency_factor' => 0.8,
        ]);
    }

    /** @test */
    public function can_start_technology_research()
    {
        $result = TechnolgiesResearchService::startResearch($this->company, $this->technology, $this->employee);
        
        $this->assertTrue($result);
        
        $this->company->refresh();
        $this->assertEquals(75000, $this->company->funds); // 100000 - 25000
    }

    /** @test */
    public function can_calculate_research_progress()
    {
        $progress = TechnolgiesResearchService::calculateResearchProgress($this->company, $this->technology);
        
        $this->assertIsNumeric($progress);
        $this->assertGreaterThanOrEqual(0, $progress);
        $this->assertLessThanOrEqual(100, $progress);
    }

    /** @test */
    public function can_handle_research_completion()
    {
        $result = TechnolgiesResearchService::completeResearch($this->company, $this->technology);
        
        $this->assertTrue($result);
        
        $this->company->refresh();
        $this->assertGreaterThan(5, $this->company->research_level);
    }

    /** @test */
    public function can_analyze_research_priorities()
    {
        $priorities = TechnolgiesResearchService::analyzeResearchPriorities($this->company);
        
        $this->assertIsArray($priorities);
        $this->assertArrayHasKey('high_priority', $priorities);
        $this->assertArrayHasKey('medium_priority', $priorities);
        $this->assertArrayHasKey('low_priority', $priorities);
    }

    /** @test */
    public function can_forecast_research_timeline()
    {
        $timeline = TechnolgiesResearchService::forecastResearchTimeline($this->company, $this->technology);
        
        $this->assertIsArray($timeline);
        $this->assertArrayHasKey('estimated_completion', $timeline);
        $this->assertArrayHasKey('milestones', $timeline);
        $this->assertArrayHasKey('resource_requirements', $timeline);
    }

    /** @test */
    public function can_handle_research_collaboration()
    {
        $collaboration = TechnolgiesResearchService::createResearchCollaboration($this->company, $this->technology);
        
        $this->assertIsArray($collaboration);
        $this->assertArrayHasKey('id', $collaboration);
        $this->assertArrayHasKey('status', $collaboration);
        $this->assertArrayHasKey('participants', $collaboration);
    }

    /** @test */
    public function can_analyze_research_efficiency()
    {
        $efficiency = TechnolgiesResearchService::analyzeResearchEfficiency($this->company);
        
        $this->assertIsArray($efficiency);
        $this->assertArrayHasKey('success_rate', $efficiency);
        $this->assertArrayHasKey('time_to_completion', $efficiency);
        $this->assertArrayHasKey('cost_efficiency', $efficiency);
    }

    /** @test */
    public function can_handle_research_funding()
    {
        $funding = TechnolgiesResearchService::allocateResearchFunding($this->company, $this->technology, 50000);
        
        $this->assertTrue($funding);
        
        $this->company->refresh();
        $this->assertEquals(50000, $this->company->funds); // 100000 - 50000
    }

    /** @test */
    public function can_validate_research_requirements()
    {
        $requirements = TechnolgiesResearchService::validateResearchRequirements($this->company, $this->technology);
        
        $this->assertIsArray($requirements);
        $this->assertArrayHasKey('funds_required', $requirements);
        $this->assertArrayHasKey('employee_skills', $requirements);
        $this->assertArrayHasKey('prerequisites', $requirements);
    }

    /** @test */
    public function can_handle_research_breakthroughs()
    {
        $breakthrough = TechnolgiesResearchService::handleResearchBreakthrough($this->company, $this->technology);
        
        $this->assertIsArray($breakthrough);
        $this->assertArrayHasKey('type', $breakthrough);
        $this->assertArrayHasKey('impact', $breakthrough);
        $this->assertArrayHasKey('bonuses', $breakthrough);
    }

    /** @test */
    public function can_analyze_technology_tree()
    {
        $technologyTree = TechnolgiesResearchService::analyzeTechnologyTree($this->company);
        
        $this->assertIsArray($technologyTree);
        $this->assertArrayHasKey('current_level', $technologyTree);
        $this->assertArrayHasKey('available_technologies', $technologyTree);
        $this->assertArrayHasKey('unlock_paths', $technologyTree);
    }

    /** @test */
    public function can_handle_research_patents()
    {
        $patent = TechnolgiesResearchService::fileResearchPatent($this->company, $this->technology);
        
        $this->assertIsArray($patent);
        $this->assertArrayHasKey('id', $patent);
        $this->assertArrayHasKey('technology', $patent);
        $this->assertArrayHasKey('filing_date', $patent);
    }
}
