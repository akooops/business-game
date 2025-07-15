<?php

namespace App\Console\Commands;

use App\Models\CompanyTechnology;
use App\Models\Technology;
use App\Models\Company;
use App\Models\Supplier;
use App\Models\Wilaya;
use Illuminate\Console\Command;
use App\Services\SettingsService;
use App\Services\TechnolgiesResearchService;
use App\Services\CalculationsService;

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

            $wilaya->update([
                'real_shipping_cost' => CalculationsService::calculatePertValue($wilaya->min_shipping_cost, $wilaya->avg_shipping_cost, $wilaya->max_shipping_cost),
            ]);

            $wilaya->save();
        }
        
        $this->info('Technologies research processing completed successfully!');
    }
} 