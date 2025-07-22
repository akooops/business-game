<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\CalculationsService;

class ProductDemand extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'min_demand' => 'decimal:3',
        'max_demand' => 'decimal:3',
        'real_demand' => 'decimal:3',
        'market_price' => 'decimal:3'
    ];

    //Relations
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    //Accessors

    //Boot
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->real_demand = CalculationsService::calcaulteRandomBetweenMinMax($model->min_demand, $model->max_demand);
        });
    }
}
