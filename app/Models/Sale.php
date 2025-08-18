<?php

namespace App\Models;

use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:3',
        'sale_price' => 'decimal:3',
        'shipping_cost' => 'decimal:3',
        'initiated_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected $appends = ['total_sale_price', 'total_shipping_cost'];

    // Statuses
    const STATUS_INITIATED = 'initiated';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    // Relations
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function wilaya()
    {
        return $this->belongsTo(Wilaya::class);
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
}
