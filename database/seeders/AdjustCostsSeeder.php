<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdjustCostsSeeder extends Seeder
{
    /**
     * Apply new storage and operations cost multipliers.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->updateProductStorageCosts();
            $this->updateMachineOperationCosts();
        });
    }

    private function updateProductStorageCosts(): void
    {
        Product::chunkById(100, function ($products) {
            foreach ($products as $product) {
                if (is_null($product->storage_cost)) {
                    continue;
                }

                $multiplier = mt_rand(150, 800) / 100;
                $newCost = round($product->storage_cost * $multiplier, 3);

                $product->updateQuietly([
                    'storage_cost' => $newCost,
                ]);

                $this->command?->line(
                    sprintf(
                        "Product #%d storage cost adjusted by %.2fx to %.3f",
                        $product->id,
                        $multiplier,
                        $newCost
                    )
                );
            }
        });
    }

    private function updateMachineOperationCosts(): void
    {
        Machine::chunkById(100, function ($machines) {
            foreach ($machines as $machine) {
                if (is_null($machine->operations_cost)) {
                    continue;
                }

                $multiplier = mt_rand(50, 125) / 100;
                $newCost = round($machine->operations_cost * $multiplier, 3);

                $machine->updateQuietly([
                    'operations_cost' => $newCost,
                ]);

                $this->command?->line(
                    sprintf(
                        "Machine #%d operations cost adjusted by %.2fx to %.3f",
                        $machine->id,
                        $multiplier,
                        $newCost
                    )
                );
            }
        });
    }
}

