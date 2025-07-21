<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Machine;
use App\Models\CompanyMachine;

class ProductionService
{
    public static function setupMachine($company, $machine)
    {
        $companyMachine = CompanyMachine::create([
            'company_id' => $company->id,
            'machine_id' => $machine->id,
            'current_reliability' => 1,
            'status' => CompanyMachine::STATUS_INACTIVE,
            'setup_at' => SettingsService::getCurrentTimestamp(),
        ]);

        FinanceService::payMachineSetupCost($company, $machine);
        NotificationService::createMachineSetupNotification($company, $machine);
    }

    public static function validateSetup($company, $machine){
        $errors = [];

        $setupCost = $machine->cost_to_acquire;

        if(!FinanceService::haveSufficientFunds($company, $setupCost)){
            $errors['funds'] = 'This company does not have enough funds to setup this machine.';
        }

        return $errors;
    }

    public static function validateAssignEmployees($company, $companyMachine, $employees){
        $errors = [];

        $machine = $companyMachine->machine;

        foreach($employees as $employee){
            if($employee->companyMachine){
                $errors['employees'] = 'This employee is already assigned to another machine. Consider unassigning this employee first.';
                continue;
            }

            if($employee->status != Employee::STATUS_ACTIVE){
                $errors['employees'] = 'This employee is not active.';
            }

            if(!$machine->machineEmployeeProfiles->contains($employee->employeeProfile)){
                $errors['employees'] = 'This machine does not need this employee profile.';
            }
        }

        return $errors;
    }
}