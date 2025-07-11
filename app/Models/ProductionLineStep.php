<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionLineStep extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
    // Relations
    public function productionLine()
    {
        return $this->belongsTo(ProductionLine::class);
    }

    public function machines()
    {
        return $this->belongsToMany(Machine::class, 'machine_production_line_steps')
                   ->withPivot('setup_time_days')
                   ->withTimestamps();
    }

    // Scopes
    public function scopeForProductionLine($query, $productionLineId)
    {
        return $query->where('production_line_id', $productionLineId);
    }

    public function scopeOrderByStep($query, $direction = 'asc')
    {
        return $query->orderBy('step', $direction);
    }

    // Accessors
}
