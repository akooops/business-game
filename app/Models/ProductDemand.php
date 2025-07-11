<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDemand extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'min_demand' => 'decimal:3',
        'max_demand' => 'decimal:3',
        'avg_demand' => 'decimal:3',
        'market_price' => 'decimal:3',
        'visibility_cost' => 'decimal:3',
        'fluctuation_factor' => 'decimal:3',
        'is_visible' => 'boolean',
    ];

    //Relations
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    //Scopes
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeInvisible($query)
    {
        return $query->where('is_visible', false);
    }

    public function scopeGameweek($query, $gameweek)
    {
        return $query->where('gameweek', $gameweek);
    }

    //Accessors
}
