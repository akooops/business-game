<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineEmployeeProfile extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'required_count' => 'integer'
    ];

    protected $appends = ['total_monthly_cost', 'total_onboarding_cost'];

    // Relations
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    // Scopes
    public function scopeForMachine($query, $machineId)
    {
        return $query->where('machine_id', $machineId);
    }

    public function scopeForEmployeeProfile($query, $employeeProfileId)
    {
        return $query->where('employee_profile_id', $employeeProfileId);
    }

    public function scopeWithMinimumCount($query, $count)
    {
        return $query->where('required_count', '>=', $count);
    }

    // Accessors
    public function getTotalMonthlyCostAttribute()
    {
        return $this->required_count * $this->employeeProfile->monthly_avg_salary;
    }

    public function getTotalOnboardingCostAttribute()
    {
        return $this->required_count * $this->employeeProfile->total_onboarding_cost;
    }
}
