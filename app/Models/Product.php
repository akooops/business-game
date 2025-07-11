<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'elasticity_coefficient' => 'decimal:3',
        'has_expiration' => 'boolean',
        'requires_rd' => 'boolean',
    ];

    protected $appends = ['type_name'];

    // Product types
    const TYPE_RAW_MATERIAL = 'raw_material';
    const TYPE_COMPONENT = 'component';
    const TYPE_FINISHED_PRODUCT = 'finished_product';

    //Relations
    public function demands()
    {
        return $this->hasMany(ProductDemand::class)->orderBy('gameweek', 'asc');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRawMaterials($query)
    {
        return $query->where('type', self::TYPE_RAW_MATERIAL);
    }

    public function scopeComponents($query)
    {
        return $query->where('type', self::TYPE_COMPONENT);
    }

    public function scopeFinishedProducts($query)
    {
        return $query->where('type', self::TYPE_FINISHED_PRODUCT);
    }

    // Accessors
    public function getTypeNameAttribute()
    {
        return match($this->type) {
            self::TYPE_RAW_MATERIAL => 'Raw Material',
            self::TYPE_COMPONENT => 'Component',
            self::TYPE_FINISHED_PRODUCT => 'Finished Product',
            default => $this->type
        };
    }
}
