<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFiles;
use Illuminate\Support\Facades\URL;
use App\Services\CalculationsService;

class Advertiser extends Model
{
    use HasFactory, HasFiles;

    protected $guarded = ['id'];

    protected $appends = ['logo_url'];

    protected $casts = [
        'min_price' => 'decimal:3',
        'max_price' => 'decimal:3',
        'real_price' => 'decimal:3',
        'min_market_impact_percentage' => 'decimal:3',
        'max_market_impact_percentage' => 'decimal:3',
    ];

    // Relations
    public function ads()
    {
        return $this->hasMany(Ad::class);
    }

    public function logo()
    {
        return $this->morphOne(File::class, 'model')->where('is_main', 1);
    }

    // Accessors
    public function getLogoUrlAttribute()
    {
        return ($this->logo) ? $this->logo->url : URL::to('assets/images/default-advertiser-logo.jpg');
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->real_price = CalculationsService::calcaulteRandomBetweenMinMax($model->min_price, $model->max_price);
        });
    }
}
