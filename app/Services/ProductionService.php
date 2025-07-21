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

    public static function validateAssignEmployee($companyMachine, $employee){
        $errors = [];

        $machine = $companyMachine->machine;

        if($machine->employee){
            $errors['employee'] = 'This machine already has an employee.';
        }

        if($employee->companyMachine){
            $errors['employee'] = 'This employee is already assigned to another machine. Consider unassigning this employee first.';
        }

        if($employee->status != Employee::STATUS_ACTIVE){
            $errors['employee'] = 'This employee is not active.';
        }

        if($machine->employeeProfile->id != $employee->employeeProfile->id){
            $errors['employee'] = 'This machine does not need this employee profile.';
        }

        return $errors;
    }

    public static function assignEmployee($companyMachine, $employee){
        $companyMachine->update([
            'employee_id' => $employee->id,
        ]);

        NotificationService::createMachineAssignedEmployeeNotification($companyMachine->company, $companyMachine->machine, $employee);
    }

    public static function unassignEmployee($companyMachine){
        $companyMachine->update([
            'employee_id' => null,
        ]);
    }
}