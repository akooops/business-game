<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Machine;
use App\Models\CompanyMachine;
use App\Models\ProductionOrder;
use App\Models\Maintenance;

class MaintenanceService
{
    public static function processMachinesReliability($company){
        $companyMachines = CompanyMachine::where('company_id', $company->id)
            ->whereIn('status', [CompanyMachine::STATUS_ACTIVE, CompanyMachine::STATUS_INACTIVE])->get();

        foreach($companyMachines as $companyMachine){
            $machine = $companyMachine->machine;
            $reliability = $companyMachine->current_reliability;

            $reliability_decay_days = $machine->reliability_decay_days;

            $reliability -= $reliability_decay_days;

            if($reliability < 0){
                $reliability = 0;
            }

            $breakChance = 0;

            $breakChance = rand(1, 100); 

            $breakThreshold = 0;
            if($reliability < 0.1){
                $breakThreshold = 75; // 75% chance
            } else if($reliability < 0.2){
                $breakThreshold = 50; // 50% chance
            } else if($reliability < 0.3){
                $breakThreshold = 25; // 25% chance
            } else if($reliability < 0.4){
                $breakThreshold = 10; // 10% chance
            }

            if($breakChance <= $breakThreshold){
                // Machine breaks
                $companyMachine->update([
                    'status' => CompanyMachine::STATUS_BROKEN,
                    'broken_at' => SettingsService::getCurrentTimestamp(),
                    'current_reliability' => $reliability
                ]);

                $productionOrders = ProductionOrder::where([
                    'company_machine_id' => $companyMachine->id,
                    'status' => ProductionOrder::STATUS_IN_PROGRESS,
                ])->get();

                foreach($productionOrders as $productionOrder){ 
                    $productionOrder->update([
                        'status' => ProductionOrder::STATUS_CANCELLED,
                    ]);
                }
                
                // Create machine broken notification
                NotificationService::createMachineBrokenNotification($companyMachine->company, $companyMachine);
                continue; // Skip reliability update since machine broke
            }

            if($reliability < 0.5){
                NotificationService::createMachineReliabilityDecreasedNotification($companyMachine->company, $companyMachine);
            }
            
            $companyMachine->update([
                'current_reliability' => $reliability
            ]);
        }
    }

    public static function validateMaintenance($companyMachine){
        $errors = [];

        if($companyMachine->status == CompanyMachine::STATUS_ACTIVE){
            $errors['machine'] = 'This machine is active, it cannot be maintained.';
        }

        if($companyMachine->status == CompanyMachine::STATUS_MAINTENANCE){
            $errors['machine'] = 'This machine is already being maintained.';
        }

        // Check if company has sufficient funds
        if (!FinanceService::haveSufficientFunds($companyMachine->company, $companyMachine->machine->avg_maintenance_cost)) {
            $errors['funds'] = 'You do not have enough funds to maintain this machine. Required: DZD ' . $companyMachine->machine->avg_maintenance_cost . ', Available: DZD ' . $companyMachine->company->funds;
        }

        return $errors;
    }

    public static function startMaintenance($companyMachine){
        $type = Maintenance::TYPE_PREDICTIVE;

        if($companyMachine->status == CompanyMachine::STATUS_BROKEN){
            $type = Maintenance::TYPE_CORRECTIVE;
        }

        // Calculate real delivery date
        $maintenanceTimeDays = CalculationsService::calculatePertValue($companyMachine->machine->min_maintenance_time_days, $companyMachine->machine->avg_maintenance_time_days, $companyMachine->machine->max_maintenance_time_days);
        $realCompletedAt = SettingsService::getCurrentTimestamp()->copy()->addDays($maintenanceTimeDays);

        $maintenance = Maintenance::create([
            'company_machine_id' => $companyMachine->id,
            'type' => $type,
            'status' => Maintenance::STATUS_IN_PROGRESS,
            'started_at' => SettingsService::getCurrentTimestamp(),
            'maintenances_cost' => $companyMachine->machine->avg_maintenance_cost,
            'completed_at' => $realCompletedAt,
        ]);

        $companyMachine->update([
            'status' => CompanyMachine::STATUS_MAINTENANCE,
        ]);

        FinanceService::payMaintenanceCost($companyMachine->company, $maintenance);
        NotificationService::createMachineMaintenanceStartedNotification($companyMachine->company, $companyMachine);
    }

    public static function completeMaintenance($maintenance){
        $maintenance->update([
            //'status' => Maintenance::STATUS_COMPLETED,
            'completed_at' => SettingsService::getCurrentTimestamp(),
        ]);

        $companyMachine = $maintenance->companyMachine;

        $companyMachine->update([
            'status' => CompanyMachine::STATUS_INACTIVE,
            'current_reliability' => min(1, $companyMachine->current_reliability + $companyMachine->current_reliability * (rand(75, 100) / 100)),
        ]);

        NotificationService::createMachineMaintenanceCompletedNotification($companyMachine->company, $companyMachine);
    }
}