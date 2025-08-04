<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:3',
        'market_impact_percentage' => 'decimal:3',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Relations
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
