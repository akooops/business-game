<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:3',
        'transaction_at' => 'datetime',
    ];

    // Types
    const TYPE_TECHNOLOGY = 'technology';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_INVENTORY = 'inventory';
    const TYPE_SALE_SHIPPING = 'sale_shipping';
    const TYPE_SALE_PAYMENT = 'sale_payment';
    const TYPE_EMPLOYEE_RECRUITMENT = 'employee_recruitment';
    const TYPE_EMPLOYEE_SALARY = 'employee_salary';
    const TYPE_MACHINE_SETUP = 'machine_setup';
    const TYPE_MACHINE_SOLD = 'machine_sold';
    const TYPE_MACHINE_OPERATIONS = 'machine_operations';
    const TYPE_MAINTENANCE = 'maintenance';
    const TYPE_MARKETING = 'marketing';
    const TYPE_LOAN_RECEIVED = 'loan_received';
    const TYPE_LOAN_PAYMENT = 'loan_payment';

    // Relations
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
