<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use App\Traits\HasFiles;

class Machine extends Model
{
    use HasFactory, HasFiles;

    protected $guarded = ['id'];

    protected $appends = ['image_url'];

    protected $casts = [
        'cost_to_acquire' => 'decimal:3',
        'area_required' => 'decimal:3',
        'hourly_energy_consumption' => 'decimal:3',
        'hourly_carbon_emissions' => 'decimal:3',
        'quality_factor' => 'decimal:3',
        'hourly_speed_min' => 'decimal:3',
        'hourly_speed_avg' => 'decimal:3',
        'hourly_speed_max' => 'decimal:3',
        'hourly_failure_chance' => 'decimal:5',
        'hourly_reliability_decay' => 'decimal:5',
        'predictive_maintenance_cost_min' => 'decimal:3',
        'predictive_maintenance_cost_avg' => 'decimal:3',
        'predictive_maintenance_cost_max' => 'decimal:3',
        'corrective_maintenance_cost_min' => 'decimal:3',
        'corrective_maintenance_cost_avg' => 'decimal:3',
        'corrective_maintenance_cost_max' => 'decimal:3',
    ];

    // Relations
    public function image()
    {
        return $this->morphOne(File::class, 'model')->where('is_main', 1);
    }

    public function employeeProfiles()
    {
        return $this->belongsToMany(EmployeeProfile::class, 'machine_employee_profiles')
                   ->withPivot('required_count')
                   ->withTimestamps();
    }

    public function outputs()
    {
        return $this->hasMany(MachineOutput::class);
    }

    public function products()  
    {
        return $this->belongsToMany(Product::class, 'machine_outputs')->withTimestamps();
    }

    // Scopes
    public function scopeByManufacturer($query, $manufacturer)
    {
        return $query->where('manufacturer', $manufacturer);
    }

    public function scopeWithinPriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice) {
            $query->where('cost_to_acquire', '>=', $minPrice);
        }
        if ($maxPrice) {
            $query->where('cost_to_acquire', '<=', $maxPrice);
        }
        return $query;
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        return ($this->image) ? $this->image->url : URL::to('assets/images/default-machine-image.jpg');
    }

    // PERT Distribution Methods
    public function calculatePertValue($optimistic, $mostLikely, $pessimistic)
    {
        // PERT formula: (O + 4*M + P) / 6
        // With some randomization around the expected value
        
        $expectedValue = ($optimistic + 4 * $mostLikely + $pessimistic) / 6;
        $standardDeviation = ($pessimistic - $optimistic) / 6;
        
        // Add some randomness using normal distribution
        $u1 = rand(0, 100000) / 100000;
        $u2 = rand(0, 100000) / 100000;
        
        $z = sqrt(-2 * log($u1)) * cos(2 * pi() * $u2);
        
        $result = $expectedValue + ($z * $standardDeviation);
        
        return max($optimistic, min($pessimistic, $result));
    }

    public function getPredictiveMaintenanceCost()
    {
        return $this->calculatePertValue(
            $this->predictive_maintenance_cost_min,
            $this->predictive_maintenance_cost_avg,
            $this->predictive_maintenance_cost_max
        );
    }

    public function getPredictiveMaintenanceDelay()
    {
        return $this->calculatePertValue(
            $this->predictive_maintenance_delay_min_hours,
            $this->predictive_maintenance_delay_avg_hours,
            $this->predictive_maintenance_delay_max_hours
        );
    }

    public function getCorrectiveMaintenanceCost()
    {
        return $this->calculatePertValue(
            $this->corrective_maintenance_cost_min,
            $this->corrective_maintenance_cost_avg,
            $this->corrective_maintenance_cost_max
        );
    }

    public function getCorrectiveMaintenanceDelay()
    {
        return $this->calculatePertValue(
            $this->corrective_maintenance_delay_min_hours,
            $this->corrective_maintenance_delay_avg_hours,
            $this->corrective_maintenance_delay_max_hours
        );
    }

    public function getRandomSpeed()
    {
        return $this->calculatePertValue(
            $this->hourly_speed_min,
            $this->hourly_speed_avg,
            $this->hourly_speed_max
        );
    }
}
