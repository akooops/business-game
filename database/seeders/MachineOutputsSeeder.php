<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\MachineOutput;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MachineOutputsSeeder extends Seeder
{
    public function run(): void
    {
        $lathe = Machine::where('name', 'Lathe')->first();
        $motor = Product::where('name', 'Motor')->first();

        if ($lathe && $motor) {
            MachineOutput::updateOrCreate(
                ['machine_id' => $lathe->id, 'product_id' => $motor->id],
                []
            );
        }
    }
}


