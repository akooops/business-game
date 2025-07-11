<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionLineOutput extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relations
    public function productionLine()
    {
        return $this->belongsTo(ProductionLine::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopeForProductionLine($query, $productionLineId)
    {
        return $query->where('production_line_id', $productionLineId);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }
}
