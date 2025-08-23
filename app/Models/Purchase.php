<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:3',
        'sale_price' => 'decimal:3',
        'shipping_cost' => 'decimal:3',
        'customs_duties' => 'decimal:3',
        'total_cost' => 'decimal:3',
        'carbon_footprint' => 'decimal:3',
        'ordered_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected $appends = ['total_sale_price', 'total_shipping_cost', 'total_customs_duties', 'total_carbon_footprint'];

    // Statuses
    const STATUS_ORDERED = 'ordered';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    // Relations
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getTotalSalePriceAttribute()
    {
        return round($this->quantity * $this->sale_price, 3) ?? 0;
    }

    public function getTotalShippingCostAttribute()
    {
        return round($this->quantity * $this->shipping_cost, 3) ?? 0;
    }

    public function getTotalCustomsDutiesAttribute()
    {
        return round(($this->total_sale_price + $this->total_shipping_cost) * $this->customs_duties, 3) ?? 0;
    }

    public function getTotalCarbonFootprintAttribute()
    {
        return round($this->quantity * $this->carbon_footprint, 3) ?? 0;
    }

    // boot
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($purchase) {
            $purchase->total_cost = $purchase->total_sale_price + $purchase->total_shipping_cost + $purchase->total_customs_duties;
        });
    }
}
