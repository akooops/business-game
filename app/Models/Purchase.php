<?php

namespace App\Models;

use App\Services\SettingsService;
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
        'estimated_delivered_at' => 'datetime',
        'real_delivered_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected $appends = ['total_sale_price', 'total_shipping_cost', 'total_customs_duties', 'total_carbon_footprint', 'is_ordered', 'order_progress'];

    // Statuses
    const STATUS_PENDING = 'pending';
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
        return $this->quantity * $this->sale_price;
    }

    public function getTotalShippingCostAttribute()
    {
        return $this->quantity * $this->shipping_cost;
    }

    public function getTotalCustomsDutiesAttribute()
    {
        return ($this->quantity + $this->total_shipping_cost) * $this->customs_duties;
    }

    public function getTotalCarbonFootprintAttribute()
    {
        return $this->quantity * $this->carbon_footprint;
    }

    public function getIsOrderedAttribute()
    {
        return $this->ordered_at && !$this->delivered_at;
    }

    public function getOrderProgressAttribute()
    {
        if(!$this->is_ordered) {
            return 100;
        }

        $currentTimestamp = SettingsService::getCurrentTimestamp();
        $orderedAt = $this->ordered_at;
        $estimatedDeliveredAt = $this->estimated_delivered_at;

        if (!$orderedAt || !$estimatedDeliveredAt) {
            return 0;
        }

        $progress = $orderedAt->diffInHours($currentTimestamp);
        $totalDays = $orderedAt->diffInHours($estimatedDeliveredAt);

        if($totalDays == 0){
            return 100;
        }

        return round(($progress / $totalDays) * 100, 2);
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
