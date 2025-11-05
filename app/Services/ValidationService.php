<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Employee;
use App\Models\CompanyMachine;
use App\Models\Loan;
use App\Models\Sale;
use App\Models\SupplierProduct;
use App\Models\ProductionOrder;

class ValidationService
{
    //-------------------------------------
    // Technologies
    //-------------------------------------
    public static function validateTechnologyResearch($company, $technology){
        $errors = [];

        // Check if company has sufficient funds
        $hasSufficientFunds = FinanceService::haveSufficientFunds($company, $technology->research_cost);
        if(!$hasSufficientFunds){
            $errors['funds'] = 'You do not have enough funds to research this technology.';
        }

        // Check if company is already researching this technology
        $alreadyResearching = $company->companyTechnologies()
            ->where('technology_id', $technology->id)
            ->exists();
        
        if ($alreadyResearching) {
            $errors['technology_id'] = 'You are already researching this technology.';
            return $errors;
        }

        // Check research level
        if ($technology->level > $company->research_level + 1) {
            $errors['technology_id'] = 'You can only research technologies up to level ' . ($company->research_level + 1) . '.';
        }

        return $errors;
    }

    //-------------------------------------
    // Products
    //-------------------------------------
    public static function validateProductSalePriceChange($company, $product){
        $errors = [];

        $companyProduct = $company->companyProducts()->where('product_id', $product->id)->first();

        if(!$companyProduct){
            $errors['product_id'] = 'This product is not in researched yet by your company';
        }

        return $errors;
    }

    //-------------------------------------
    // Purchases
    //-------------------------------------
    public static function validatePurchase($company, $supplier, $product, $quantity){
        $errors = [];

        if(!$supplier){
            $errors['supplier_id'] = 'The selected supplier does not exist.';
        }

        if(!$product){
            $errors['product_id'] = 'The selected product does not exist.';
        }

        if(!$product->is_researched){
            $errors['product_id'] = 'This product is not researched yet.';
        }

        $totalCost = ProcurementService::calcaulteTotalCost($supplier, $product, $quantity);
        $hasSufficientFunds = FinanceService::haveSufficientFunds($company, $totalCost);

        if(!$hasSufficientFunds){
            $errors['funds'] = 'You do not have enough funds to purchase this product.';
        }

        if($supplier->country && !$supplier->country->allows_imports) {
            $errors['country_id'] = 'This supplier is in a country that our government blocked imports from.';
        }

        $supplierProduct = SupplierProduct::where([
            'supplier_id' => $supplier->id, 
            'product_id' => $product->id
        ])->first();

        if(!$supplierProduct) {
            $errors['supplier_product'] = 'This supplier does not sell this product.';
        }
        
        return $errors;
    }

    //-------------------------------------
    // Sales
    //-------------------------------------
    public static function validateSaleConfirmation($company, $sale){
        $errors = [];

        // Check if the sale is available for delivery
        if($sale->status != Sale::STATUS_INITIATED){
            $errors['status'] = 'This sale is not available for delivery.';
        }

        // Check if the company has enough stock of the product
        $hasSufficientStock = InventoryService::haveSufficientStock($company, $sale->product, $sale->quantity);
        if(!$hasSufficientStock){
            $errors['stock'] = 'This company does not have enough stock of this product.';
        }

        // Check if the company sells the product
        $companyProduct = $sale->company->companyProducts()->where('product_id', $sale->product->id)->first();

        if(!$companyProduct) {
            $errors['company_product'] = 'This company does not sell this product.';
        }

        return $errors;
    }

    //-------------------------------------
    // Employees
    //-------------------------------------
    public static function validateEmployeeRecruitment($employee){
        $errors = [];

        $recruitmentCost = $employee->recruitment_cost;

        // Check if the company has enough funds to recruit the employee
        $hasSufficientFunds = FinanceService::haveSufficientFunds($employee->company, $recruitmentCost);
        if(!$hasSufficientFunds){
            $errors['funds'] = 'This company does not have enough funds to recruit this employee.';
        }

        // Check if the employee is available for recruitment
        if($employee->status != Employee::STATUS_APPLIED){
            $errors['status'] = 'This employee is not available for recruitment.';
        }
        
        return $errors;
    }

    public static function validateEmployeePromotion($employee){    
        $errors = [];

        if($employee->status != Employee::STATUS_ACTIVE){
            $errors['status'] = 'This employee is not active.';
        }

        return $errors;
    }

    public static function validateEmployeeFiring($employee){
        $errors = [];

        if($employee->status != Employee::STATUS_ACTIVE){
            $errors['status'] = 'This employee is not active.';
        }

        if($employee->companyMachine){
            if($employee->companyMachine->status == CompanyMachine::STATUS_ACTIVE){
                $errors['companyMachine'] = 'This employee is assigned to a machine that is active. Wait until the production is completed.';
            }
        }

        return $errors;
    }

    //-------------------------------------
    // Machines
    //-------------------------------------
    public static function validateMachineSetup($company, $machine){
        $errors = [];

        $setupCost = $machine->cost_to_acquire;

        $hasSufficientFunds = FinanceService::haveSufficientFunds($company, $setupCost);

        if(!$hasSufficientFunds){
            $errors['funds'] = 'This company does not have enough funds to setup this machine.';
        }

        return $errors;
    }  
    
    public static function validateAssignEmployee($companyMachine, $employee){
        $errors = [];

        $machine = $companyMachine->machine;

        if(!$employee){
            $errors['employee'] = 'Employee not found.';
        }

        if($companyMachine->employee){
            $errors['employee'] = 'This machine already has an employee.';
        }

        if($employee->companyMachine){
            $errors['employee'] = 'This employee is already assigned to another machine. Consider unassigning this employee first.';
        }

        if($companyMachine->status != CompanyMachine::STATUS_INACTIVE){
            $errors['machine'] = 'This machine is not inactive, it cannot be assigned to an employee.';
        }

        if($employee->status != Employee::STATUS_ACTIVE){
            $errors['employee'] = 'This employee is not active.';
        }

        if($machine->employeeProfile->id != $employee->employeeProfile->id){
            $errors['employee'] = 'This machine does not need this employee profile.';
        }

        return $errors;
    }

    //-------------------------------------
    // Maintenance
    //-------------------------------------
    public static function validateMaintenance($companyMachine){
        $errors = [];

        if($companyMachine->status == CompanyMachine::STATUS_ACTIVE){
            $errors['machine'] = 'This machine is active, it cannot be maintained.';
        }

        if($companyMachine->status == CompanyMachine::STATUS_MAINTENANCE){
            $errors['machine'] = 'This machine is already being maintained.';
        }

        // Check if company has sufficient funds
        $hasSufficientFunds = FinanceService::haveSufficientFunds($companyMachine->company, $companyMachine->mmaintenance_cost);
        if (!$hasSufficientFunds) {
            $errors['funds'] = 'You do not have enough funds to maintain this machine.';
        }
        
        return $errors;
    }

    //-------------------------------------
    // Production Orders
    //-------------------------------------
    public static function validateProductionOrder($companyMachine, $product, $quantity){
        $errors = [];

        //product is reseached
        if(!$product->is_researched) {
            $errors['product_researched'] = 'This product is not researched yet.';
        }

        //machine can produce this product
        $checkMachineCanProduceProduct = $companyMachine->machine->outputs()->where('product_id', $product->id)->exists();
        if(!$checkMachineCanProduceProduct){
            $errors['product'] = 'This machine does not produce this product.';
        }

        //Machine should be inactive
        if($companyMachine->status != CompanyMachine::STATUS_INACTIVE){
            $errors['machine'] = 'This machine is not active.';
        }

        //Machine should have an employee
        if(!$companyMachine->employee){
            $errors['employee'] = 'This machine does not have an employee.';
        }

        //Check if company has enough materials to produce the product
        $productRecipes = $product->recipes;
        
        $missingMaterials = [];
        foreach($productRecipes as $recipe){
            $material = $recipe->material;
            $requiredQuantity = $recipe->quantity * $quantity;
            
            // Round to 3 decimals to match available_stock precision (users purchase in 3 decimals)
            $requiredQuantity = round($requiredQuantity, 3);

            // Get available stock
            $companyProduct = $companyMachine->company->companyProducts()->where('product_id', $material->id)->first();
            $availableStock = $companyProduct ? $companyProduct->available_stock : 0;
            
            // Allow 0.001 tolerance (3 digits) to handle precision differences
            $missingQuantity = $requiredQuantity - $availableStock;
            
            // Only show error if missing more than 0.001
            if($missingQuantity > 0.001){
                $missingMaterials[] = $material->name . ' (missing: ' . number_format($missingQuantity, 3) . ', have: ' . number_format($availableStock, 3) . ', need: ' . number_format($requiredQuantity, 3) . ')';
            }
        }

        if(!empty($missingMaterials)){
            $errors['material'] = 'Insufficient stock of: ' . implode(', ', $missingMaterials);
        }

        return $errors;
    }   

    public static function validateCancelProductionOrder($company, $productionOrder){
        $errors = [];

        if($productionOrder->status != ProductionOrder::STATUS_IN_PROGRESS){
            $errors['status'] = 'This production order is not in progress.';
        }

        return $errors;
    }

    public static function validateMachineSell($companyMachine){
        $errors = [];

        if($companyMachine->status == CompanyMachine::STATUS_SOLD){
            $errors['machine'] = 'This machine is already sold.';
        }

        if(!SettingsService::getAbilityToSellMachines()){
            $errors['machine'] = 'The system blocks the selling of machines.';
        }

        return $errors;
    }

    //-------------------------------------
    // Loans
    //-------------------------------------
    public static function validateBorrowMoney($bank, $amount, $company){
        $errors = [];

        if(!$bank){
            $errors['bank'] = 'Bank not found.';
        }

        // Calculate total loans from this bank for this company
        $existingLoansTotal = Loan::where('company_id', $company->id)
            ->where('bank_id', $bank->id)
            ->sum('amount');

        // Check if new loan amount + existing loans exceeds bank's max
        $totalAfterLoan = $existingLoansTotal + $amount;

        if($totalAfterLoan > $bank->loan_max_amount){
            $remaining = $bank->loan_max_amount - $existingLoansTotal;
            $errors['amount'] = 'This bank can not lend you more than his maximum loan amount. You already have ' . number_format($existingLoansTotal, 2) . ' DZD from this bank. Maximum remaining: ' . number_format($remaining, 2) . ' DZD.';
        }

        return $errors;
    }   

    public static function validatePayLoan($loan){
        $errors = [];

        if($loan->is_paid){
            $errors['loan'] = 'This loan is already paid.';
        }

        $hasSufficientFunds = FinanceService::haveSufficientFunds($loan->company, $loan->remaining_amount);
        
        if(!$hasSufficientFunds){
            $errors['funds'] = 'This company does not have enough funds to pay this loan. You need to borrow more money.';
        }

        return $errors;
    }

    //-------------------------------------
    // Advertisers
    //-------------------------------------
    public static function validateAdPackagePurchase($company, $advertiser){
        $errors = [];

        $hasSufficientFunds = FinanceService::haveSufficientFunds($company, $advertiser->real_price);
        
        if(!$hasSufficientFunds){
            $errors['funds'] = 'This company does not have enough funds to purchase this ad package.';
        }

        return $errors;
    }
}