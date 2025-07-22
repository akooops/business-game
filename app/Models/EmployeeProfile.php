<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFiles;
use App\Services\CalculationsService;

class EmployeeProfile extends Model
{
    use HasFactory, HasFiles;

    protected $guarded = ['id'];

    protected $casts = [
        'min_salary_month' => 'decimal:3',
        'max_salary_month' => 'decimal:3',
        'min_recruitment_cost' => 'decimal:3',
        'max_recruitment_cost' => 'decimal:3',
    ];

    //Relations
}
