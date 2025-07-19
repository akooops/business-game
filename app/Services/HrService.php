<?php

namespace App\Services;

use App\Models\Employee;

class HrService
{
    public static function generateEmployees($company, $employeeProfile){
        $numberOfEmployees = rand(1, 10);

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
            $base_mood_decay_lambda = rand(1, 100) / 100;
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

    public static function recruitEmployee($employee){       
        $employee->update([
            'status' => Employee::STATUS_HIRED,
            'hired_at' => SettingsService::getCurrentTimestamp(),
        ]);

        FinanceService::payEmployeeRecruitmentCost($employee->company, $employee);
        NotificationService::createEmployeeHiredNotification($employee);
    }
}