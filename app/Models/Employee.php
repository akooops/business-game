<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFiles;

class Employee extends Model
{
    use HasFactory, HasFiles;

    protected $guarded = ['id'];

    protected $casts = [
        'salary_month' => 'decimal:3',
        'current_mood' => 'decimal:3',
        'mood_decay_rate_days' => 'decimal:3',
        'efficiency_factor' => 'decimal:3',
        'applied_at' => 'datetime',
        'hired_at' => 'datetime',
        'fired_at' => 'datetime',
        'last_promotion_at' => 'datetime',
    ];

    const STATUS_APPLIED = 'applied';
    const STATUS_HIRED = 'hired';
    const STATUS_FIRED = 'fired';
    const STATUS_PROMOTED = 'promoted';

    //Relations
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }
}
