<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFiles;

class ProductionLine extends Model
{
    use HasFactory, HasFiles;

    protected $guarded = ['id'];

    protected $casts = [
        'area_required' => 'decimal:3'
    ];

    protected $appends = ['total_steps'];

    // Relations
    public function outputs()
    {
        return $this->hasMany(ProductionLineOutput::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'production_line_outputs');
    }

    public function steps()
    {
        return $this->hasMany(ProductionLineStep::class)->orderBy('step', 'asc');
    }

    public function machines()
    {
        return $this->belongsToMany(Machine::class, 'machine_production_line_steps')
                   ->withPivot('setup_time_days')
                   ->withTimestamps();
    }

    // Scopes
    public function scopeWithArea($query, $minArea = null, $maxArea = null)
    {
        if ($minArea) {
            $query->where('area_required', '>=', $minArea);
        }
        if ($maxArea) {
            $query->where('area_required', '<=', $maxArea);
        }
        return $query;
    }

    // Accessors
    public function getTotalStepsAttribute()
    {
        return $this->steps()->count();
    }
}
