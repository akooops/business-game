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
        'unpaid_loans' => 'decimal:3',
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

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function companyMachines()
    {
        return $this->hasMany(CompanyMachine::class);
    }   

    public function machines()
    {
        return $this->belongsToMany(Machine::class, 'company_machines', 'company_id', 'machine_id')
            ->withPivot('status', 'current_reliability', 'setup_at', 'last_maintenance_at', 'last_broken_at')
            ->withTimestamps();
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function ads()
    {
        return $this->hasMany(Ad::class);
    }

    

    // Accessors
}
