<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'customs_duties_rate' => 'decimal:5',
        'tva_rate' => 'decimal:5',
        'insurance_rate' => 'decimal:5',
        'freight_cost' => 'decimal:5',
        'port_handling_fee' => 'decimal:5',
        'allows_imports' => 'boolean',
    ];

    // Scopes
    public function scopeAllowsImports($query)
    {
        return $query->where('allows_imports', true);
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

    // Simple utility methods
    public function getEffectiveTaxRate($productCost)
    {
        $calculation = $this->calculateImportCost($productCost);
        return $calculation['tax_percentage'];
    }

    public function formatCurrency($amount)
    {
        return number_format($amount, 2);
    }
}
