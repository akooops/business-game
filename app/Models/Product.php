<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use App\Traits\HasFiles;

class Product extends Model
{
    use HasFactory, HasFiles;

    protected $guarded = ['id'];

    protected $casts = [
        'elasticity_coefficient' => 'decimal:3',
        'has_expiration' => 'boolean',
        'need_technology' => 'boolean',
    ];

    protected $appends = ['type_name', 'image_url'];

    // Product types
    const TYPE_RAW_MATERIAL = 'raw_material';
    const TYPE_COMPONENT = 'component';
    const TYPE_FINISHED_PRODUCT = 'finished_product';

    //Relations
    public function image()
    {
        return $this->morphOne(File::class, 'model')->where('is_main', 1);
    }

    public function technology()
    {
        return $this->belongsTo(Technology::class);
    }

    public function demands()
    {
        return $this->hasMany(ProductDemand::class)->orderBy('gameweek', 'asc');
    }

    public function recipes()
    {
        return $this->hasMany(ProductRecipe::class);
    }

    public function machineOutputs()
    {
        return $this->hasMany(MachineOutput::class);
    }

    public function machines()
    {
        return $this->belongsToMany(Machine::class, 'machine_outputs')->withTimestamps();
    }

    public function supplierProducts()
    {
        return $this->hasMany(SupplierProduct::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'supplier_products')
            ->withPivot([
                'min_sale_price',
                'avg_sale_price',
                'max_sale_price',
                'minimum_order_qty',
                'carbon_footprint'
            ])
            ->withTimestamps();
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

    public function getImageUrlAttribute()
    {
        return ($this->image) ? $this->image->url : URL::to('assets/images/default-product-image.jpg');
    }
}
