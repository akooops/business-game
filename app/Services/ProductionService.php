<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Machine;
use App\Models\CompanyMachine;
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

    public static function validateProductionOrder($companyMachine, $product, $quantity){
        $errors = [];

        //product is reseached
        if(!$product->is_researched) {
            $errors['product_researched'] = 'This product is not researched yet.';
        }

        if(!$companyMachine->machine->products->contains($product)){
            $errors['product'] = 'This machine does not produce this product.';
        }

        if($companyMachine->status != CompanyMachine::STATUS_INACTIVE){
            $errors['machine'] = 'This machine is not active.';
        }

        if(!$companyMachine->employee){
            $errors['employee'] = 'This machine does not have an employee.';
        }

        $productRecipes = $product->recipes;

        foreach($productRecipes as $recipe){
            $material = $recipe->material;
            $requiredQuantity = $recipe->quantity * $quantity;

            if(!InventoryService::haveSufficientStock($companyMachine->company, $material, $requiredQuantity)){
                $errors['material'] = 'This company does not have enough stock of ' . $material->name . ' to produce this product.';
            }
        }

        return $errors;
    }

    public static function startProduction($companyMachine, $product, $quantity){
        $machine = $companyMachine->machine;

        $realSpeed = CalculationsService::calculatePertValue($machine->min_speed, $machine->avg_speed, $machine->max_speed);
        $realSpeed = $realSpeed * $companyMachine->employee->efficiency_factor;

        if($realSpeed < $machine->min_speed){
            $realSpeed = $machine->min_speed;
        }

        if($realSpeed > $machine->max_speed){
            $realSpeed = $machine->max_speed;
        }

        $expectedSpeed = $machine->avg_speed;

        $estimatedProductionTime = $quantity / $expectedSpeed;
        $realProductionTime = $quantity / $realSpeed;

        $estimatedCompletedAt = SettingsService::getCurrentTimestamp()->addDays($estimatedProductionTime);
        $realCompletedAt = SettingsService::getCurrentTimestamp()->addDays($realProductionTime);

        $productionOrder = ProductionOrder::create([
            'company_machine_id' => $companyMachine->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'status' => ProductionOrder::STATUS_IN_PROGRESS,
            'started_at' => SettingsService::getCurrentTimestamp(),
            'estimated_completed_at' => $estimatedCompletedAt,
            'real_completed_at' => $realCompletedAt,
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

        NotificationService::createMachineProductionStartedNotification($companyMachine->company, $companyMachine->machine, $product, $quantity);
    }

    public static function completeProduction($productionOrder){
        $productionOrder->update([
            'status' => ProductionOrder::STATUS_COMPLETED,
            'completed_at' => SettingsService::getCurrentTimestamp(),
        ]);

        $companyMachine = $productionOrder->companyMachine;

        $companyMachine->update([
            'status' => CompanyMachine::STATUS_INACTIVE,
        ]);

        $machine = $companyMachine->machine;

        $company = $companyMachine->company;

        $company->update([
            'carbon_footprint' => $company->carbon_footprint + $productionOrder->quantity * $machine->carbon_footprint,
        ]);

        InventoryService::productionCompleted($productionOrder, $productionOrder->quantity * $machine->quality_factor);
        NotificationService::createMachineProductionCompletedNotification($companyMachine->company, $companyMachine->machine, $productionOrder->product, $productionOrder->quantity, $machine->quality_factor);
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
}