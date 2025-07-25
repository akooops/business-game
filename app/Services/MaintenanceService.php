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
        // Get all company machines that are active or inactive
        $companyMachines = CompanyMachine::where('company_id', $company->id)
            ->whereIn('status', [CompanyMachine::STATUS_ACTIVE, CompanyMachine::STATUS_INACTIVE])->get();

        // Process each company machine
        foreach($companyMachines as $companyMachine){
            $machine = $companyMachine->machine;
            $reliability = $companyMachine->current_reliability;

            // Calculate reliability decay
            $reliability_decay_days = $machine->reliability_decay_days;

            $reliability -= $reliability_decay_days;

            // If reliability is less than 0, set it to 0
            if($reliability < 0){
                $reliability = 0;
            }

            // Calculate break chance
            $breakChance = rand(1, 100); 

            // Calculate break threshold
            $breakThreshold = 0;

            // If reliability is less than 0.1, set break threshold to 75%
            if($reliability < 0.1){
                $breakThreshold = 75; // 75% chance
            } 
            // If reliability is less than 0.2, set break threshold to 50%
            else if($reliability < 0.2){
                $breakThreshold = 50; // 50% chance
            } 
            // If reliability is less than 0.3, set break threshold to 25%
            else if($reliability < 0.3){
                $breakThreshold = 25; // 25% chance
            } 
            // If reliability is less than 0.4, set break threshold to 10%
            else if($reliability < 0.4){
                $breakThreshold = 10; // 10% chance
            }

            // If break chance is less than or equal to break threshold, machine breaks
            if($breakChance <= $breakThreshold){
                // Machine breaks
                $companyMachine->update([
                    'status' => CompanyMachine::STATUS_BROKEN,
                    'broken_at' => SettingsService::getCurrentTimestamp(),
                    'current_reliability' => $reliability
                ]);

                // Cancel all production orders for the company machine
                $productionOrders = ProductionOrder::where([
                    'company_machine_id' => $companyMachine->id,
                    'status' => ProductionOrder::STATUS_IN_PROGRESS,
                ])->get();

                // Cancel each production order
                foreach($productionOrders as $productionOrder){ 
                    $productionOrder->update([
                        'status' => ProductionOrder::STATUS_CANCELLED,
                    ]);
                }
                
                // Create machine broken notification
                NotificationService::createMachineBrokenNotification($companyMachine->company, $companyMachine);
                continue; // Skip reliability update since machine broke
            }

            // If reliability is less than 0.5, create machine reliability decreased notification
            if($reliability < 0.5){
                NotificationService::createMachineReliabilityDecreasedNotification($companyMachine->company, $companyMachine);
            }
            
            $companyMachine->update([
                'current_reliability' => $reliability
            ]);
        }
    }

    public static function startMaintenance($companyMachine){
        $type = Maintenance::TYPE_PREDICTIVE;

        if($companyMachine->status == CompanyMachine::STATUS_BROKEN){
            $type = Maintenance::TYPE_CORRECTIVE;
        }

        // Calculate real delivery date
        $maintenance = Maintenance::create([
            'type' => $type,
            'status' => Maintenance::STATUS_IN_PROGRESS,
            'started_at' => SettingsService::getCurrentTimestamp(),
            'maintenances_cost' => $companyMachine->maintenance_cost,
            'maintenance_time_days' => $companyMachine->maintenance_time_days,
            'company_machine_id' => $companyMachine->id,
        ]);

        $companyMachine->update([
            'status' => CompanyMachine::STATUS_MAINTENANCE,
        ]);

        FinanceService::payMaintenanceCost($companyMachine->company, $maintenance);
        NotificationService::createMachineMaintenanceStartedNotification($companyMachine->company, $companyMachine);
    }

    public static function completeMaintenance($company){
        // Get all company machines that are in maintenance
        $companyMachines = $company->companyMachines;

        foreach($companyMachines as $companyMachine){
            // Get all maintenances for the company machine that are in progress
            $companyMaintenances = Maintenance::where([
                'company_machine_id' => $companyMachine->id, 
                'status' => Maintenance::STATUS_IN_PROGRESS
            ])->get();

            foreach($companyMaintenances as $companyMaintenance){
                // Check if maintenance is completed
                $currentTimestamp = SettingsService::getCurrentTimestamp();
                $completedAt = $companyMaintenance->started_at->copy()->addDays($companyMaintenance->maintenance_time_days);

                // If maintenance is completed, update the maintenance and company machine
                if($completedAt <= $currentTimestamp){
                    $companyMaintenance->update([
                        'status' => Maintenance::STATUS_COMPLETED,
                        'completed_at' => $currentTimestamp,
                    ]);

                    // Update company machine status and reliability
                    $companyMachine->update([
                        'status' => CompanyMachine::STATUS_INACTIVE,
                        'current_reliability' => min(1, $companyMachine->current_reliability + $companyMachine->current_reliability * (rand(75, 100) / 100)),
                    ]);

                    // Create machine maintenance completed notification
                    NotificationService::createMachineMaintenanceCompletedNotification($companyMachine->company, $companyMachine);
                }
            }
        }
    }
}