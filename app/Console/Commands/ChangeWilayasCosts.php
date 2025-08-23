<?php

namespace App\Console\Commands;

use App\Models\Wilaya;
use Illuminate\Console\Command;
use App\Services\SalesService;

class ChangeWilayasCosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:change-wilayas-costs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change wilayas costs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Changing wilayas costs...');

        $wilayas = Wilaya::all();

        foreach($wilayas as $wilaya){
            $this->info('Processing wilaya: ' . $wilaya->name);

            SalesService::changeWilayaShippingCosts($wilaya);
        }
        
        $this->info('Wilayas costs changed successfully!');
    }
} 