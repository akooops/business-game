<?php

namespace App\Services;

use App\Models\Employee;
use App\Services\SettingsService;

class HrService
{
    public static function generateEmployees($company, $employeeProfile){
        $numberOfEmployees = rand(1, 4);

        $employees = [];

        for($i = 0; $i < $numberOfEmployees; $i++){
            $salary = CalculationsService::calculatePertValue($employeeProfile->min_salary_month, $employeeProfile->avg_salary_month, $employeeProfile->max_salary_month);

            // Add salary variation (-10% to +10%)
            $salaryVariation = rand(-10, 10) / 100;
            $salaryVariation = $salary * $salaryVariation;
            $realSalary = $salary + $salaryVariation;

            if($realSalary < $employeeProfile->min_salary_month){
                $realSalary = $employeeProfile->min_salary_month;
            }
            
            if($realSalary > $employeeProfile->max_salary_month){
                $realSalary = $employeeProfile->max_salary_month;
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
                'current_mood' => 1,
                'mood_decay_rate_days' => $mood_decay_rate_days,
                'efficiency_factor' => $efficiency_factor,
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

    public static function validateRecruitment($employee){
        $errors = [];

        $recruitmentCost = $employee->employeeProfile->real_recruitment_cost;

        if(!FinanceService::haveSufficientFunds($employee->company, $recruitmentCost)){
            $errors['funds'] = 'This company does not have enough funds to recruit this employee.';
        }

        if($employee->status != Employee::STATUS_APPLIED){
            $errors['status'] = 'This employee is not available for recruitment.';
        }
        
        return $errors;
    }

    public static function validatePromotion($employee){    
        $errors = [];

        if($employee->status != Employee::STATUS_ACTIVE){
            $errors['status'] = 'This employee is not active.';
        }

        return $errors;
    }

    public static function validateFiring($employee){
        $errors = [];

        if($employee->status != Employee::STATUS_ACTIVE){
            $errors['status'] = 'This employee is not active.';
        }

        return $errors;
    }

    public static function recruitEmployee($employee){       
        $employee->update([
            'status' => Employee::STATUS_ACTIVE,
            'hired_at' => SettingsService::getCurrentTimestamp(),
        ]);

        FinanceService::payEmployeeRecruitmentCost($employee->company, $employee);
        NotificationService::createEmployeeHiredNotification($employee);
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

            // Check for resignation if mood is below 40%
            if($mood < 0.4){
                // Calculate resignation probability based on mood
                // Lower mood = higher probability
                $baseProbability = 5; // 5% base chance
                $moodMultiplier = (0.4 - $mood) / 0.4; // 0 to 1 multiplier
                $resignationProbability = $baseProbability + ($moodMultiplier * 15); // Max 20% at mood 0
                
                $resignationChance = rand(1, 100);
                if($resignationChance <= $resignationProbability){
                    // Employee resigns
                    $employee->update([
                        'status' => Employee::STATUS_RESIGNED,
                        'resigned_at' => SettingsService::getCurrentTimestamp(),
                        'current_mood' => $mood
                    ]);
                    
                    // Create resignation notification
                    NotificationService::createEmployeeResignedNotification($employee);
                    continue; // Skip mood update since employee resigned
                }
            }else if($mood < 0.5){
                NotificationService::createEmployeeMoodDecreasedNotification($employee);
            }
            
            $employee->update([
                'current_mood' => $mood
            ]);
        }
    }

    public static function promoteEmployee($employee, $newSalary){
        $factor = $newSalary / $employee->salary_month;
        $mood_decay_rate_days = $employee->mood_decay_rate_days / $factor;

        $employee->update([
            'salary_month' => $newSalary,
            'current_mood' => min(1, $employee->current_mood * $factor),
            'efficiency_factor' => min(4, $employee->efficiency_factor * $factor),
            'mood_decay_rate_days' => $mood_decay_rate_days,
            'last_promotion_at' => SettingsService::getCurrentTimestamp(),
        ]);
    }

    public static function fireEmployee($employee){
        $employee->update([
            'status' => Employee::STATUS_FIRED,
            'fired_at' => SettingsService::getCurrentTimestamp(),
        ]);

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
}