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
        'energy_consumption_hour' => 'decimal:3',
        'carbon_emissions_hour' => 'decimal:3',
        'quality_factor' => 'decimal:3',
        'min_speed_hour' => 'decimal:3',
        'avg_speed_hour' => 'decimal:3',
        'max_speed_hour' => 'decimal:3',
        'failure_chance_hour' => 'decimal:5',
        'reliability_decay_hour' => 'decimal:5',
        'min_predictive_maintenance_cost' => 'decimal:3',
        'avg_predictive_maintenance_cost' => 'decimal:3',
        'max_predictive_maintenance_cost' => 'decimal:3',
        'min_corrective_maintenance_cost' => 'decimal:3',
        'avg_corrective_maintenance_cost' => 'decimal:3',
        'max_corrective_maintenance_cost' => 'decimal:3',
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
            $this->min_predictive_maintenance_cost,
            $this->avg_predictive_maintenance_cost,
            $this->max_predictive_maintenance_cost
        );
    }

    public function getPredictiveMaintenanceDelay()
    {
        return $this->calculatePertValue(
            $this->min_predictive_maintenance_delay_hours,
            $this->avg_predictive_maintenance_delay_hours,
            $this->max_predictive_maintenance_delay_hours
        );
    }

    public function getCorrectiveMaintenanceCost()
    {
        return $this->calculatePertValue(
            $this->min_corrective_maintenance_cost,
            $this->avg_corrective_maintenance_cost,
            $this->max_corrective_maintenance_cost
        );
    }

    public function getCorrectiveMaintenanceDelay()
    {
        return $this->calculatePertValue(
            $this->min_corrective_maintenance_delay_hours,
            $this->avg_corrective_maintenance_delay_hours,
            $this->max_corrective_maintenance_delay_hours
        );
    }

    public function getRandomSpeed()
    {
        return $this->calculatePertValue(
            $this->min_speed_hour,
            $this->avg_speed_hour,
            $this->max_speed_hour
        );
    }
}
