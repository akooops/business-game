<?php

namespace App\Models;

use App\Models\File;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Country extends Model
{
    use HasFactory, HasFiles;

    protected $guarded = ['id'];

    protected $appends = ['flag_url'];

    protected $casts = [
        'min_shipping_cost' => 'decimal:3',
        'max_shipping_cost' => 'decimal:3',
        'avg_shipping_cost' => 'decimal:3',
        'customs_duties_rate' => 'decimal:3',
        'tva_rate' => 'decimal:3',
        'insurance_rate' => 'decimal:3',
        'freight_cost' => 'decimal:3',
        'port_handling_fee' => 'decimal:3',
        'allows_imports' => 'boolean',
    ];

    //Relations
    public function flag()
    {
        return $this->morphOne(File::class, 'model')->where('is_main', 1);
    }

    // Methods
    public function getFlagUrlAttribute()
    {
        return ($this->flag) ? $this->flag->url : URL::to('assets/images/default-country-flag.jpg');
    }
    
    // Main calculation method for total import cost
    public function calculateImportCost($productCost, $weight = 100)
    {
        // Step 1: Calculate freight and insurance
        $freight = $this->freight_cost;
        $insurance = ($productCost * $this->insurance_rate) / 100;
        
        // Step 2: Calculate CIF value
        $cif = $productCost + $freight + $insurance;
        
        // Step 3: Calculate taxes
        $customsDuties = ($cif * $this->customs_duties_rate) / 100;
        $tva = (($cif + $customsDuties) * $this->tva_rate) / 100;
        $handlingFee = $this->port_handling_fee;
        
        // Step 4: Calculate total
        $totalTaxes = $customsDuties + $tva + $handlingFee;
        $totalCost = $cif + $totalTaxes;
        
        return [
            'product_cost' => $productCost,
            'freight_cost' => $freight,
            'insurance_cost' => $insurance,
            'cif_value' => $cif,
            'customs_duties' => $customsDuties,
            'tva' => $tva,
            'handling_fee' => $handlingFee,
            'total_taxes' => $totalTaxes,
            'total_cost' => $totalCost,
            'tax_percentage' => ($totalTaxes / $cif) * 100
        ];
    }
}
