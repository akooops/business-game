<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use App\Traits\HasFiles;

class Loan extends Model
{
    use HasFactory, HasFiles;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:3',
        'interest_rate' => 'decimal:3',
        'total_amount' => 'decimal:3',
        'monthly_payment' => 'decimal:3',
        'remaining_amount' => 'decimal:3',
        'is_paid' => 'boolean',
    ];

    // Relations
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
