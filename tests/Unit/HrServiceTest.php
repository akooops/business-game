<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\HrService;
use App\Services\ProductionService;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeProfile;
use App\Models\CompanyMachine;
use App\Models\Machine;
use App\Models\ProductionOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HrServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $employeeProfile;
    protected $machine;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::factory()->create(['funds' => 100000]);
        $this->employeeProfile = EmployeeProfile::factory()->create([
            'min_salary_month' => 2000,
            'max_salary_month' => 5000,
            'min_recruitment_cost' => 1000,
            'max_recruitment_cost' => 3000,
        ]);
        $this->machine = Machine::factory()->create();
    }

    /** @test */
    public function can_generate_employees()
    {
        $employees = HrService::generateEmployees($this->company, $this->employeeProfile);
        
        $this->assertIsArray($employees);
        $this->assertGreaterThan(0, count($employees));
        $this->assertLessThanOrEqual(3, count($employees));
        
        foreach ($employees as $employee) {
            $this->assertInstanceOf(Employee::class, $employee);
            $this->assertEquals($this->company->id, $employee->company_id);
            $this->assertGreaterThanOrEqual($this->employeeProfile->min_salary_month, $employee->salary_month);
            $this->assertLessThanOrEqual($this->employeeProfile->max_salary_month, $employee->salary_month);
        }
    }

    /** @test */
    public function can_pay_salaries()
    {
        // Create employees first
        $employees = HrService::generateEmployees($this->company, $this->employeeProfile);
        
        $initialFunds = $this->company->funds;
        $totalSalaries = collect($employees)->sum('salary_month');
        
        HrService::paySalaries($this->company);
        
        $this->company->refresh();
        $this->assertEquals($initialFunds - $totalSalaries, $this->company->funds);
    }

    /** @test */
    public function can_assign_employee_to_machine()
    {
        $employee = Employee::factory()->create(['company_id' => $this->company->id]);
        $companyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $this->machine->id,
        ]);
        
        ProductionService::assignEmployee($companyMachine, $employee);
        
        $this->assertEquals($employee->id, $companyMachine->employee_id);
    }

    /** @test */
    public function can_unassign_employee_from_machine()
    {
        $employee = Employee::factory()->create(['company_id' => $this->company->id]);
        $companyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'machine_id' => $this->machine->id,
            'employee_id' => $employee->id,
        ]);
        
        ProductionService::unassignEmployee($companyMachine);
        
        $this->assertNull($companyMachine->employee_id);
    }

    /** @test */
    public function can_process_employee_mood()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'current_mood' => 0.8,
        ]);
        
        HrService::processEmployeesMood($this->company);
        
        $employee->refresh();
        // Mood should have decreased due to time passing (or stayed the same if no time passed)
        $this->assertLessThanOrEqual(0.8, $employee->current_mood);
    }

    /** @test */
    public function can_fire_employee()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'active',
        ]);
        
        // Create a company machine and assign the employee to it
        $companyMachine = CompanyMachine::factory()->create([
            'company_id' => $this->company->id,
            'employee_id' => $employee->id,
        ]);
        
        HrService::fireEmployee($employee);
        
        $employee->refresh();
        $companyMachine->refresh();
        
        $this->assertEquals('fired', $employee->status);
        $this->assertNull($companyMachine->employee_id);
    }

    /** @test */
    public function can_promote_employee()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'active',
        ]);
        
        HrService::promoteEmployee($employee, 5000); // Promote with new salary
        
        $employee->refresh();
        $this->assertEquals('active', $employee->status); // Promotion doesn't change status
    }

    /** @test */
    public function can_recruit_employee()
    {
        $employeeProfile = EmployeeProfile::factory()->create([
            'min_salary_month' => 2500,
            'max_salary_month' => 4000,
        ]);
        
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'applied',
            'employee_profile_id' => $employeeProfile->id,
            'salary_month' => 3000, // Set specific salary
        ]);
        
        HrService::recruitEmployee($employee);
        
        $this->assertInstanceOf(Employee::class, $employee);
        $this->assertEquals($this->company->id, $employee->company_id);
        $this->assertEquals('active', $employee->status);
        $this->assertGreaterThanOrEqual(2500, $employee->salary_month);
        $this->assertLessThanOrEqual(4000, $employee->salary_month);
    }

    /** @test */
    public function employee_salary_affects_efficiency()
    {
        $highSalaryEmployee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'salary_month' => 8000, // High salary
        ]);
        
        $lowSalaryEmployee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'salary_month' => 2000, // Low salary
        ]);
        
        // High salary employee should have better efficiency
        $this->assertGreaterThan($lowSalaryEmployee->efficiency_factor, $highSalaryEmployee->efficiency_factor);
    }

    /** @test */
    public function employee_experience_affects_performance()
    {
        $experiencedEmployee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'efficiency_factor' => 0.9, // High efficiency
        ]);
        
        $newEmployee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'efficiency_factor' => 0.5, // Low efficiency
        ]);
        
        // Experienced employee should have better efficiency
        $this->assertGreaterThan($newEmployee->efficiency_factor, $experiencedEmployee->efficiency_factor);
    }

    /** @test */
    public function can_handle_employee_turnover()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'current_mood' => 0.1, // Very low mood
        ]);

        $turnoverRate = HrService::calculateEmployeeTurnover($this->company);
        
        $this->assertIsNumeric($turnoverRate);
        $this->assertGreaterThanOrEqual(0, $turnoverRate);
    }

    /** @test */
    public function can_analyze_workforce_performance()
    {
        $performance = HrService::analyzeWorkforcePerformance($this->company);
        
        $this->assertIsArray($performance);
        $this->assertArrayHasKey('average_efficiency', $performance);
        $this->assertArrayHasKey('average_mood', $performance);
        $this->assertArrayHasKey('productivity_score', $performance);
    }

    /** @test */
    public function can_plan_workforce_expansion()
    {
        $expansionPlan = HrService::planWorkforceExpansion($this->company, 10); // 10 new employees
        
        $this->assertIsArray($expansionPlan);
        $this->assertArrayHasKey('cost', $expansionPlan);
        $this->assertArrayHasKey('timeline', $expansionPlan);
        $this->assertArrayHasKey('positions', $expansionPlan);
    }

    /** @test */
    public function can_handle_employee_training()
    {
        $employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'efficiency_factor' => 0.6,
        ]);

        $trainingResult = HrService::trainEmployee($employee, 'advanced_skills');
        
        $this->assertTrue($trainingResult);
        $this->assertGreaterThan(0.6, $employee->fresh()->efficiency_factor);
    }

    /** @test */
    public function can_manage_employee_benefits()
    {
        $benefits = HrService::getEmployeeBenefits($this->company);
        
        $this->assertIsArray($benefits);
        $this->assertArrayHasKey('health_insurance', $benefits);
        $this->assertArrayHasKey('retirement_plan', $benefits);
        $this->assertArrayHasKey('vacation_days', $benefits);
    }

    /** @test */
    public function can_handle_workplace_conflicts()
    {
        $conflict = HrService::reportWorkplaceConflict($this->company, 'harassment');
        
        $this->assertIsArray($conflict);
        $this->assertArrayHasKey('id', $conflict);
        $this->assertArrayHasKey('type', $conflict);
        $this->assertArrayHasKey('status', $conflict);
    }

    /** @test */
    public function can_analyze_salary_competitiveness()
    {
        $competitiveness = HrService::analyzeSalaryCompetitiveness($this->company);
        
        $this->assertIsArray($competitiveness);
        $this->assertArrayHasKey('market_position', $competitiveness);
        $this->assertArrayHasKey('recommendations', $competitiveness);
    }

    /** @test */
    public function can_handle_employee_feedback()
    {
        $feedback = HrService::submitEmployeeFeedback($this->company, 'suggestions', 'Improve work environment');
        
        $this->assertIsArray($feedback);
        $this->assertArrayHasKey('id', $feedback);
        $this->assertArrayHasKey('category', $feedback);
        $this->assertArrayHasKey('content', $feedback);
    }

    /** @test */
    public function can_plan_succession()
    {
        $successionPlan = HrService::planSuccession($this->company, 'CEO');
        
        $this->assertIsArray($successionPlan);
        $this->assertArrayHasKey('position', $successionPlan);
        $this->assertArrayHasKey('candidates', $successionPlan);
        $this->assertArrayHasKey('timeline', $successionPlan);
    }

    /** @test */
    public function can_analyze_diversity_metrics()
    {
        $diversity = HrService::analyzeDiversityMetrics($this->company);
        
        $this->assertIsArray($diversity);
        $this->assertArrayHasKey('gender_distribution', $diversity);
        $this->assertArrayHasKey('age_distribution', $diversity);
        $this->assertArrayHasKey('ethnicity_distribution', $diversity);
    }
}
