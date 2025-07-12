<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wilaya extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'min_shipping_cost',
        'max_shipping_cost',
        'avg_shipping_cost',
        'min_shipping_time_days',
        'avg_shipping_time_days',
        'max_shipping_time_days',
        'tva_rate',
    ];

    protected $casts = [
        'min_shipping_cost' => 'decimal:5',
        'max_shipping_cost' => 'decimal:5',
        'avg_shipping_cost' => 'decimal:5',
        'min_shipping_time_days' => 'integer',
        'avg_shipping_time_days' => 'integer',
        'max_shipping_time_days' => 'integer',
        'tva_rate' => 'decimal:5',
    ];

    /**
     * Calculate shipping cost based on weight (using avg as base)
     */
    public function calculateShippingCost($weight_kg = 100)
    {
        $base_cost = $this->avg_shipping_cost;
        
        // Simple weight scaling: base cost is for 100kg
        $weight_multiplier = $weight_kg / 100;
        
        return $base_cost * $weight_multiplier;
    }

    /**
     * Get shipping cost range as string
     */
    public function getShippingRangeText()
    {
        return $this->min_shipping_cost . ' - ' . $this->max_shipping_cost . ' DZD';
    }

    /**
     * Get delivery time range as string
     */
    public function getDeliveryTimeRangeText()
    {
        return $this->min_shipping_time_days . ' - ' . $this->max_shipping_time_days . ' days';
    }

    /**
     * Calculate total cost including TVA
     */
    public function calculateTotalCost($shipping_cost)
    {
        $tva_amount = $shipping_cost * ($this->tva_rate / 100);
        return $shipping_cost + $tva_amount;
    }
} 