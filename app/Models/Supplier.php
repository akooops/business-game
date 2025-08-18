<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\URL;
use App\Traits\HasFiles;
use App\Services\CalculationsService;

class Supplier extends Model
{
    use HasFactory, HasFiles;

    protected $guarded = ['id'];

    protected $casts = [
        'min_shipping_cost' => 'decimal:3',
        'max_shipping_cost' => 'decimal:3',
        'real_shipping_cost' => 'decimal:3',
        'carbon_footprint' => 'decimal:3',
    ];

    protected $appends = ['image_url', 'type_name', 'location_name'];

    // Supplier types
    const TYPE_INTERNATIONAL = 'international';
    const TYPE_LOCAL = 'local';

    // Relations
    public function image()
    {
        return $this->morphOne(File::class, 'model')->where('is_main', 1);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function wilaya()
    {
        return $this->belongsTo(Wilaya::class);
    }

    public function supplierProducts()
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'supplier_products')
            ->withPivot([
                'min_sale_price',
                'max_sale_price',
                'real_sale_price',
                'min_shipping_cost',
                'max_shipping_cost',
                'real_shipping_cost',
                'min_shipping_time_days',
                'max_shipping_time_days',
                'real_shipping_time_days',
                'carbon_footprint',
            ])
            ->withTimestamps();
    }

    // Accessors
    public function getTypeNameAttribute()
    {
        return $this->is_international ? 'International' : 'Local';
    }

    public function getImageUrlAttribute()
    {
        return ($this->image) ? $this->image->url : URL::to('assets/images/default-supplier-image.jpg');
    }

    public function getLocationNameAttribute()
    {
        if ($this->is_international && $this->country) {
            return $this->country->name;
        }
        
        if (!$this->is_international && $this->wilaya) {
            return $this->wilaya->name;
        }
        
        return 'Unknown Location';
    }

    //Boot
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->real_shipping_cost = CalculationsService::calcaulteRandomBetweenMinMax($model->min_shipping_cost, $model->max_shipping_cost);
            $model->real_shipping_time_days = CalculationsService::calcaulteRandomBetweenMinMax($model->min_shipping_time_days, $model->max_shipping_time_days);
        });
    }
} 