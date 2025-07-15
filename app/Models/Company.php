<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'funds' => 'decimal:3',
        'carbon_footprint' => 'decimal:3',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function companyTechnologies()
    {
        return $this->hasMany(CompanyTechnology::class);
    }

    public function technologies()
    {
        return $this->belongsToMany(Technology::class, 'company_technologies', 'company_id', 'technology_id')
            ->withPivot('started_at', 'estimated_completed_at', 'completed_at')
            ->orderBy('level', 'asc')
            ->withTimestamps();
    }

    public function companyProducts()
    {
        return $this->hasMany(CompanyProduct::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'company_products', 'company_id', 'product_id')
            ->withPivot('total_stock', 'in_sale_stock', 'sale_price')
            ->withTimestamps();
    }

    // Accessors
}
