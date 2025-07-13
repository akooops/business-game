<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wilaya extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'min_shipping_cost_per_unit' => 'decimal:3',
        'max_shipping_cost_per_unit' => 'decimal:3',
        'avg_shipping_cost_per_unit' => 'decimal:3',
        'real_shipping_cost_per_unit' => 'decimal:3',
    ];

    //Relations
    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }
} 