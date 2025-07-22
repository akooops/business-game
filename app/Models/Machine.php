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
        'operations_cost' => 'decimal:3',
        'carbon_footprint' => 'decimal:3',
        'quality_factor' => 'decimal:3',
        'min_speed' => 'decimal:3',
        'max_speed' => 'decimal:3',
        'reliability_decay_days' => 'decimal:3',
        'min_maintenance_cost' => 'decimal:3',
        'max_maintenance_cost' => 'decimal:3',
    ];

    // Relations
    public function image()
    {
        return $this->morphOne(File::class, 'model')->where('is_main', 1);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function outputs()
    {
        return $this->hasMany(MachineOutput::class);
    }

    public function products()  
    {
        return $this->belongsToMany(Product::class, 'machine_outputs')->withTimestamps();
    }
    
    // Accessors
    public function getImageUrlAttribute()
    {
        return ($this->image) ? $this->image->url : URL::to('assets/images/default-machine-image.jpg');
    }
}
