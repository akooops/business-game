<?php

namespace App\Services;

use App\Models\CompanyMachine;
use App\Models\Employee;
use App\Models\ProductionOrder;
use App\Services\SettingsService;

class HrService
{
    public static function generateEmployees($company, $employeeProfile){
        $numberOfEmployees = rand(1, 3);

        $employees = [];

        for($i = 0; $i < $numberOfEmployees; $i++){
            $salary = CalculationsService::calcaulteRandomBetweenMinMax($employeeProfile->min_salary_month, $employeeProfile->max_salary_month);
            $recruitmentCost = CalculationsService::calcaulteRandomBetweenMinMax($employeeProfile->min_recruitment_cost, $employeeProfile->max_recruitment_cost);

            $realSalary = self::addVariationToSalary($salary);
            $realRecruitmentCost = self::addVariationToRecruitmentCost($recruitmentCost);

            if($realSalary < $employeeProfile->min_salary_month){
                $realSalary = $employeeProfile->min_salary_month;
            }
            
            if($realSalary > $employeeProfile->max_salary_month){
                $realSalary = $employeeProfile->max_salary_month;
            }

            if($realRecruitmentCost < $employeeProfile->min_recruitment_cost){
                $realRecruitmentCost = $employeeProfile->min_recruitment_cost;
            }
            
            if($realRecruitmentCost > $employeeProfile->max_recruitment_cost){
                $realRecruitmentCost = $employeeProfile->max_recruitment_cost;
            }

            // Calculate factor based on real salary vs expected salary
            $factor = $realSalary / $salary;

            // Calculate mood decay rate (lambda for exponential distribution)
            // Higher salary means slower decay (smaller lambda)
            $base_mood_decay_lambda = rand(1, 5) / 350;
            $mood_decay_rate_days = $base_mood_decay_lambda / $factor;

            // Calculate efficiency factor as simple multiplier for machine speed
            // Higher salary means better efficiency (higher multiplier)
            $base_efficiency = rand(50, 150) / 100; // 0.5 to 1.5 base efficiency
            $efficiency_factor = $base_efficiency * $factor;

            $employee = Employee::create([
                'name' => self::getRandomName(),
                'salary_month' => $realSalary,
                'recruitment_cost' => $realRecruitmentCost,
                'current_mood' => 1,
                'mood_decay_rate_days' => $mood_decay_rate_days,
                'efficiency_factor' => min(2, $efficiency_factor),
                'status' => Employee::STATUS_APPLIED,
                'applied_at' => SettingsService::getCurrentTimestamp(),
                'timelimit_days' => rand(1, 7),
                'company_id' => $company->id,
                'employee_profile_id' => $employeeProfile->id,
            ]);

            $employees[] = $employee;
        }

        return $employees;
    }

    public static function addVariationToSalary($salary){
        // Add salary variation (-10% to +10%)
        $salaryVariation = rand(-10, 10) / 100;
        $salaryVariation = $salary * $salaryVariation;
        $realSalary = round($salary + $salaryVariation);

        return $realSalary;
    }

    public static function addVariationToRecruitmentCost($recruitmentCost){
        $variation = rand(-10, 10) / 100;
        $recruitmentCostVariation = $recruitmentCost * $variation;
        $realRecruitmentCost = round($recruitmentCost + $recruitmentCostVariation);

        return $realRecruitmentCost;
    }

    public static function getRandomName(){
        $names = [
            // Male names
            'Ahmed', 'Mohammed', 'Ali', 'Omar', 'Youssef', 'Karim', 'Samir', 'Nabil', 'Rachid', 'Hassan',
            'Abdelkader', 'Mustapha', 'Said', 'Brahim', 'Djamel', 'Farid', 'Hakim', 'Ibrahim', 'Jamal', 'Khalil',
            'Lamine', 'Malik', 'Nassim', 'Oussama', 'Riyad', 'Sofiane', 'Tarek', 'Wassim', 'Yacine', 'Zakaria',
            'Adel', 'Bilal', 'Chakib', 'Djamal', 'El Hadi', 'Fares', 'Ghassan', 'Hamza', 'Idris', 'Jawad',
            'Kais', 'Lyes', 'Mounir', 'Nadir', 'Othman', 'Pascal', 'Qassim', 'Rachid', 'Salah', 'Tayeb'
        ];
        
        return $names[array_rand($names)];
    }

    public static function recruitEmployee($employee){    
        // Recruit the employee
        $employee->update([
            'status' => Employee::STATUS_ACTIVE,
            'hired_at' => SettingsService::getCurrentTimestamp(),
        ]);

        // Pay the recruitment cost
        FinanceService::payEmployeeRecruitmentCost($employee->company, $employee);
        NotificationService::createEmployeeHiredNotification($employee->company, $employee->employeeProfile, $employee);
    }

    public static function paySalaries($company){
        $totalSalaries = $company->employees()->where('status', Employee::STATUS_ACTIVE)->sum('salary_month');

        FinanceService::payEmployeesSalary($company, $totalSalaries);
        NotificationService::createEmployeeSalaryPaidNotification($company, $totalSalaries);
    }

    public static function processEmployeesMood($company){
        $employees = $company->employees()->where('status', Employee::STATUS_ACTIVE)->get();

        foreach($employees as $employee){
            $mood = $employee->current_mood;
            $mood_decay_rate_days = $employee->mood_decay_rate_days;
            
            // Calculate mood decay: subtract the decay rate from current mood
            // Both values are between 0 and 1, so we just subtract directly
            $mood -= $mood_decay_rate_days;
            
            // Ensure mood doesn't go below 0
            if($mood < 0){
                $mood = 0;
            }

            $resignationChance = 0;

            $resignationChance = rand(1, 100); 

            $resignationThreshold = 0;
            if($mood < 0.1){
                $resignationThreshold = 75; // 75% chance
            } else if($mood < 0.2){
                $resignationThreshold = 50; // 50% chance
            } else if($mood < 0.3){
                $resignationThreshold = 25; // 25% chance
            } else if($mood < 0.4){
                $resignationThreshold = 10; // 10% chance
            } else {
                $resignationThreshold = 5; // 5% chance
            }

            if($resignationChance <= $resignationThreshold){
                // Employee resigns
                $employee->update([
                    'status' => Employee::STATUS_RESIGNED,
                    'resigned_at' => SettingsService::getCurrentTimestamp(),
                    'current_mood' => $mood
                ]);

                $employee->companyMachine->update([
                    'employee_id' => null,
                ]);

                $productionOrders = ProductionOrder::where([
                    'company_machine_id' => $employee->companyMachine->id,
                    'status' => ProductionOrder::STATUS_IN_PROGRESS,
                ])->get();

                foreach($productionOrders as $productionOrder){ 
                    $productionOrder->update([
                        'status' => ProductionOrder::STATUS_CANCELLED,
                    ]);
                }

                // Create resignation notification
                NotificationService::createEmployeeResignedNotification($employee->company, $employee);
                continue; // Skip mood update since employee resigned
            }

            if($mood < 0.5){
                NotificationService::createEmployeeMoodDecreasedNotification($employee->company, $employee);
            }
            
            $employee->update([
                'current_mood' => $mood
            ]);
        }
    }

    public static function promoteEmployee($employee, $newSalary){
        // Promote the employee
        $factor = $newSalary / $employee->salary_month;
        $mood_decay_rate_days = $employee->mood_decay_rate_days / $factor;

        // Update the employee's salary, mood, efficiency, and mood decay rate
        $employee->update([
            'salary_month' => $newSalary,
            'current_mood' => min(1, $employee->current_mood * $factor),
            'efficiency_factor' => min(4, $employee->efficiency_factor * $factor),
            'mood_decay_rate_days' => $mood_decay_rate_days,
            'last_promotion_at' => SettingsService::getCurrentTimestamp(),
        ]);
    }

    public static function fireEmployee($employee){
        // Fire the employee
        $employee->update([
            'status' => Employee::STATUS_FIRED,
            'fired_at' => SettingsService::getCurrentTimestamp(),
        ]);

        // Remove the employee from the machine
        $employee->companyMachine->update([
            'employee_id' => null,
        ]);

        // Decrease the mood of the other employees
        $companyEmployees = $employee->company->employees()->where('status', Employee::STATUS_ACTIVE)->get();

        foreach($companyEmployees as $companyEmployee){
            // Decrease mood by random amount between 5% to 15%
            $moodDecrease = rand(5, 15) / 100; // Convert to decimal (0.05 to 0.15)
            $newMood = $companyEmployee->current_mood - $moodDecrease;
            
            // Ensure mood doesn't go below 0
            if($newMood < 0){
                $newMood = 0;
            }
            
            $companyEmployee->update([
                'current_mood' => $newMood,
            ]);
        }
    }

    public static function processAppliedEmployees($company){
        // Process applied employees
        $appliedEmployees = $company->employees()->where('status', Employee::STATUS_APPLIED)->get();

        foreach($appliedEmployees as $employee){
            $currentTimestamp = SettingsService::getCurrentTimestamp();
            $appliedAt = $employee->applied_at;
            $timeLimitDays = $employee->timelimit_days;
            
            // Check if employee has exceeded its time limit
            if($appliedAt->addDays($timeLimitDays) <= $currentTimestamp){
                $employee->delete();
            }
        }
    }
}