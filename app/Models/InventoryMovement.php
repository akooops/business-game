<?php

namespace App\Models;

use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:3',
        'moved_at' => 'datetime',
    ];

    // Statuses
    const MOVEMENT_TYPE_IN = 'in';
    const MOVEMENT_TYPE_OUT = 'out';
    const MOVEMENT_TYPE_EXPIRED = 'expired';
    const MOVEMENT_TYPE_DAMAGED = 'damaged';
    const MOVEMENT_TYPE_LOST = 'lost';

    // Relations
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
