<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRecipe extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    //Relations
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function material()
    {
        return $this->belongsTo(Product::class, 'material_id');
    }

    //Scopes
    public function scopeProduct($query, $product)
    {
        return $query->where('product_id', $product);
    }

    public function scopeMaterial($query, $material)
    {
        return $query->where('material_id', $material);
    }
}
