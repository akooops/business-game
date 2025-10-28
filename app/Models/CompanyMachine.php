<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use App\Traits\HasFiles;

class CompanyMachine extends Model
{
    use HasFactory, HasFiles;

    protected $guarded = ['id'];

    protected $casts = [
        'speed' => 'decimal:3',
        'quality_factor' => 'decimal:3',
        'operations_cost' => 'decimal:3',
        'carbon_footprint' => 'decimal:3',
        'reliability_decay_days' => 'decimal:3',
        'maintenance_cost' => 'decimal:3',
        'maintenance_time_days' => 'integer',
        'current_reliability' => 'decimal:3',
        'loss_on_sale_days' => 'decimal:3',
        'acquisition_cost' => 'decimal:3',
        'current_value' => 'decimal:3',
        'setup_at' => 'datetime',
        'last_maintenance_at' => 'datetime',
        'last_broken_at' => 'datetime',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_BROKEN = 'broken';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_SOLD = 'sold';

    // Relations
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class);
    }

    public function ongoingProductionOrder()
    {
        return $this->hasOne(ProductionOrder::class)->where('status', ProductionOrder::STATUS_IN_PROGRESS);
    }
}
