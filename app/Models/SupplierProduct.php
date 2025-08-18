<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\CalculationsService;

class SupplierProduct extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'min_sale_price' => 'decimal:3',
        'max_sale_price' => 'decimal:3',
        'real_sale_price' => 'decimal:3',
    ];
    // Relations
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    //Boot
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->real_sale_price = CalculationsService::calcaulteRandomBetweenMinMax($model->min_sale_price, $model->max_sale_price);
        });
    }
}
