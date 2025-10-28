<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Database\Seeder;

class InventoryMovementsSeeder extends Seeder
{
    public function run(): void
    {
        // Get all companies
        $companies = Company::all();
        
        if ($companies->isEmpty()) {
            $this->command->info('No companies found. Please seed companies first.');
            return;
        }

        // Get all products
        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->info('No products found. Please seed products first.');
            return;
        }

        // Movement types
        $movementTypes = ['in', 'out', 'expired', 'damaged', 'lost'];
        
        // Create movements for each company
        foreach ($companies as $company) {
            // Create 5-15 movements per product for each company
            foreach ($products as $product) {
                $numMovements = rand(5, 15);
                
                for ($i = 0; $i < $numMovements; $i++) {
                    $movementType = $movementTypes[array_rand($movementTypes)];
                    
                    // Determine quantity based on product type
                    $maxQuantity = 1000;
                    if ($product->type === 'finished_product') {
                        $maxQuantity = 500;
                    } elseif ($product->type === 'component') {
                        $maxQuantity = 300;
                    } else {
                        $maxQuantity = 1000;
                    }
                    
                    $quantity = rand(10, $maxQuantity);
                    
                    // Determine which quantity is original vs current
                    if ($movementType === 'in') {
                        $originalQuantity = $quantity;
                        $currentQuantity = $quantity;
                    } elseif ($movementType === 'out') {
                        $originalQuantity = $quantity + rand(0, 100);
                        $currentQuantity = $quantity;
                    } else {
                        // For expired, damaged, lost - both are the same
                        $originalQuantity = $quantity;
                        $currentQuantity = 0;
                    }
                    
                    // Random date within last 6 months
                    $movedAt = now()->subDays(rand(0, 180))->subHours(rand(0, 23));
                    
                    // Generate notes
                    $notes = null;
                    if ($movementType === 'in') {
                        $notes = ['Received from supplier', 'Production output', 'Inventory stock', 'Delivery received'][array_rand(['Received from supplier', 'Production output', 'Inventory stock', 'Delivery received'])];
                    } elseif ($movementType === 'out') {
                        $notes = ['Sold to customer', 'Used in production', 'Shipment sent', 'Consumed internally'][array_rand(['Sold to customer', 'Used in production', 'Shipment sent', 'Consumed internally'])];
                    } elseif ($movementType === 'expired') {
                        $notes = 'Products expired - removed from inventory';
                    } elseif ($movementType === 'damaged') {
                        $notes = 'Products damaged - removed from inventory';
                    } elseif ($movementType === 'lost') {
                        $notes = 'Products lost - removed from inventory';
                    }
                    
                    InventoryMovement::create([
                        'company_id' => $company->id,
                        'product_id' => $product->id,
                        'movement_type' => $movementType,
                        'original_quantity' => $originalQuantity,
                        'current_quantity' => $currentQuantity,
                        'notes' => $notes,
                        'moved_at' => $movedAt,
                        'reference_type' => null,
                        'reference_id' => null,
                    ]);
                }
            }
        }
    }
}

