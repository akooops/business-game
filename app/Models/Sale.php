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
        'initiated_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    protected $appends = ['total_sale_price'];

    // Statuses
    const STATUS_INITIATED = 'initiated';
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
}
