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
        'recruitment_cost' => 'decimal:3',
        'current_mood' => 'decimal:3',
        'mood_decay_rate_days' => 'decimal:3',
        'efficiency_factor' => 'decimal:3',
        'applied_at' => 'datetime',
        'hired_at' => 'datetime',
        'fired_at' => 'datetime',
        'resigned_at' => 'datetime',
        'last_promotion_at' => 'datetime',
    ];

    protected $appends = ['fullname'];

    const STATUS_APPLIED = 'applied';
    const STATUS_ACTIVE = 'active';
    const STATUS_FIRED = 'fired';
    const STATUS_RESIGNED = 'resigned';

    //Relations
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    public function companyMachine()
    {
        return $this->hasOne(CompanyMachine::class);
    }

    // Accessors
    public function getFullnameAttribute(): string
    {
        $first = trim((string) ($this->firstname ?? ''));
        $last = trim((string) ($this->lastname ?? ''));
        if ($first !== '' || $last !== '') {
            return trim($first . ' ' . $last);
        }
        return (string) ($this->name ?? '');
    }
}
