<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Machine;
use App\Models\CompanyMachine;
use App\Models\Maintenance;
use App\Models\ProductionOrder;

class ProductionService
{
    public static function setupMachine($company, $machine)
    {
        $speed = CalculationsService::calcaulteRandomBetweenMinMax($machine->min_speed, $machine->max_speed);
        $maintenance_cost = CalculationsService::calcaulteRandomBetweenMinMax($machine->min_maintenance_cost, $machine->max_maintenance_cost);
        $maintenance_time_days = CalculationsService::calcaulteRandomBetweenMinMax($machine->min_maintenance_time_days, $machine->max_maintenance_time_days);

        $companyMachine = CompanyMachine::create([
            'speed' => $speed,
            'quality_factor' => $machine->quality_factor,
            'carbon_footprint' => $machine->carbon_footprint,
            'operations_cost' => $machine->operations_cost,
            'reliability_decay_days' => $machine->reliability_decay_days,
            'loss_on_sale_days' => $machine->loss_on_sale_days,
            'acquisition_cost' => $machine->cost_to_acquire,
            'current_value' => $machine->cost_to_acquire,
            'maintenance_cost' => $maintenance_cost,
            'maintenance_time_days' => $maintenance_time_days,
            'current_reliability' => 1,
            'status' => CompanyMachine::STATUS_INACTIVE,
            'setup_at' => SettingsService::getCurrentTimestamp(),
            'company_id' => $company->id,
            'machine_id' => $machine->id,
        ]);

        FinanceService::payMachineSetupCost($company, $machine);
        NotificationService::createMachineSetupNotification($company, $machine);
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

    public static function startProduction($companyMachine, $product, $quantity){
        $machine = $companyMachine->machine;

        $realSpeed = $companyMachine->speed;
        $realSpeed = $realSpeed * $companyMachine->employee_efficiency_factor;

        if($realSpeed < $machine->min_speed){
            $realSpeed = $machine->min_speed;
        }

        if($realSpeed > $machine->max_speed){
            $realSpeed = $machine->max_speed;
        }

        $timeToComplete = $quantity / $realSpeed;

        if($timeToComplete < 1){
            $timeToComplete = 1;
        }

        
        $productionOrder = ProductionOrder::create([
            'quantity' => $quantity,
            'time_to_complete' => $timeToComplete,
            'quality_factor' => $companyMachine->quality_factor,
            'employee_efficiency_factor' => $companyMachine->employee->efficiency_factor,
            'carbon_footprint' => $companyMachine->carbon_footprint,
            'status' => ProductionOrder::STATUS_IN_PROGRESS,
            'started_at' => SettingsService::getCurrentTimestamp(),
            'company_machine_id' => $companyMachine->id,
            'product_id' => $product->id,
        ]);

        $companyMachine->update([
            'status' => CompanyMachine::STATUS_ACTIVE,
        ]);
        
        $productRecipes = $product->recipes;

        foreach($productRecipes as $recipe){
            $material = $recipe->material;
            $requiredQuantity = $recipe->quantity * $quantity;

            InventoryService::productionStarted($productionOrder, $material, $requiredQuantity);
        }

        $carbonFootprint = $companyMachine->carbon_footprint * $quantity;
        PollutionService::releaseCarbonFootprint($companyMachine->company, $carbonFootprint);

        NotificationService::createMachineProductionStartedNotification($companyMachine->company, $companyMachine->machine, $product, $quantity);
    }

    public static function completeProduction($company){
        //Get all company machines
        $companyMachines = CompanyMachine::where('company_id', $company->id)->get();

        foreach($companyMachines as $companyMachine){
            //Get all production orders for the company machine
            $productionOrders = $companyMachine->productionOrders()->where('status', ProductionOrder::STATUS_IN_PROGRESS)->get();

            foreach($productionOrders as $productionOrder){
                $currentTimestamp = SettingsService::getCurrentTimestamp();
                $completedAt = $productionOrder->started_at->copy()->addDays($productionOrder->time_to_complete);

                //Check if production order is completed
                if($completedAt <= $currentTimestamp){                    
                    //Update production order status to completed
                    $productionOrder->update([
                        'status' => ProductionOrder::STATUS_COMPLETED,
                        'completed_at' => SettingsService::getCurrentTimestamp(),
                    ]);
                    
                    $companyMachine->update([
                        'status' => CompanyMachine::STATUS_INACTIVE,
                    ]);
            
                    InventoryService::productionCompleted($productionOrder, $productionOrder->quantity * $productionOrder->quality_factor);
                    NotificationService::createMachineProductionCompletedNotification($companyMachine->company, $companyMachine->machine, $productionOrder->product, $productionOrder->quantity, $companyMachine->quality_factor);
                }
            }
        }
    }

    public static function cancelProductionOrder($productionOrder){
        $productionOrder->update([
            'status' => ProductionOrder::STATUS_CANCELLED,
        ]);

        $companyMachine = $productionOrder->companyMachine;
        $companyMachine->update([
            'status' => CompanyMachine::STATUS_INACTIVE,
        ]);
    }

    public static function payMachineOperationCost($company){
        $machines = $company->machines;
        $totalCost = 0;

        foreach($machines as $machine){
            $totalCost += $machine->operations_cost;
        }

        FinanceService::payMachineOperationCost($company, $totalCost);
        NotificationService::createMachineOperationCostsPaidNotification($company, $totalCost);
    }

    public static function calculateMachinesValue($company){
        $companyMachines = CompanyMachine::where('company_id', $company->id)->get();

        foreach($companyMachines as $companyMachine){
            $currentValue = $companyMachine->current_value;
            $lossOnSaleDays = $companyMachine->loss_on_sale_days;
            $currentTimestamp = SettingsService::getCurrentTimestamp();
            $setupAt = $companyMachine->setup_at;

            $timeSinceSetup = $currentTimestamp->diffInDays($setupAt);

            if($timeSinceSetup > 0){
                $valueLoss = $currentValue * $lossOnSaleDays * $timeSinceSetup;
                $currentValue -= $valueLoss;

                $companyMachine->update([
                    'current_value' => $currentValue,
                ]);
            }
        }
    }

    public static function sellMachine($companyMachine){
        $brokenFactor = 0;  

        if($companyMachine->status == CompanyMachine::STATUS_BROKEN){
            $brokenFactor = rand(1, 30) / 100;
        } else if($companyMachine->status == CompanyMachine::STATUS_MAINTENANCE){
            $brokenFactor = rand(1, 5) / 100;
        }

        $companyMachine->update([
            'status' => CompanyMachine::STATUS_SOLD,
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

        // Unassign employee
        self::unassignEmployee($companyMachine);

        // Cancel all maintenances for the company machine
        $maintenances = Maintenance::where([
            'company_machine_id' => $companyMachine->id, 
            'status' => Maintenance::STATUS_IN_PROGRESS,
        ])->get();

        foreach($maintenances as $maintenance){
            $maintenance->update([
                'status' => Maintenance::STATUS_CANCELLED,
            ]);
        }

        $soldPrice = $companyMachine->current_value * (1 - $brokenFactor);

        // Receive machine sale
        FinanceService::receiveMachineSale($companyMachine->company, $soldPrice);
        NotificationService::createMachineSoldNotification($companyMachine->company, $companyMachine, $soldPrice);
    }
}