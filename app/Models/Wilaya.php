<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\CalculationsService;

class Wilaya extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'min_shipping_cost' => 'decimal:3',
        'max_shipping_cost' => 'decimal:3',
        'real_shipping_cost' => 'decimal:3',
    ];

    //Relations
    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    //Boot
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->real_shipping_time_days = (int) round(CalculationsService::calcaulteRandomBetweenMinMax($model->min_shipping_time_days, $model->max_shipping_time_days));
            $model->real_shipping_cost = CalculationsService::calcaulteRandomBetweenMinMax($model->min_shipping_cost, $model->max_shipping_cost);
        });
    }
} 