<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineProductionLineStep extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'setup_time_days' => 'integer'
    ];

    protected $appends = ['setup_time_hours', 'setup_complexity'];

    // Relations
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function productionLineStep()
    {
        return $this->belongsTo(ProductionLineStep::class);
    }

    // Scopes
    public function scopeForMachine($query, $machineId)
    {
        return $query->where('machine_id', $machineId);
    }

    public function scopeForProductionLineStep($query, $stepId)
    {
        return $query->where('production_line_step_id', $stepId);
    }

    public function scopeQuickSetup($query, $maxDays = 1)
    {
        return $query->where('setup_time_days', '<=', $maxDays);
    }

    public function scopeComplexSetup($query, $minDays = 7)
    {
        return $query->where('setup_time_days', '>=', $minDays);
    }

    // Accessors
    public function getSetupTimeHoursAttribute()
    {
        return $this->setup_time_days * 8; // Assuming 8 hours per day
    }

    public function getSetupComplexityAttribute()
    {
        return match(true) {
            $this->setup_time_days <= 1 => 'Simple',
            $this->setup_time_days <= 3 => 'Moderate',
            $this->setup_time_days <= 7 => 'Complex',
            default => 'Very Complex'
        };
    }
}
