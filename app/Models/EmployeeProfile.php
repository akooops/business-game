<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'skills' => 'array',
        'monthly_min_salary' => 'float',
        'monthly_avg_salary' => 'float',
        'monthly_max_salary' => 'float',
        'recruitment_cost_per_employee' => 'float',
        'training_cost_per_employee' => 'float',
        'training_duration_days' => 'integer'
    ];

    protected $appends = ['difficulty_level', 'salary_range', 'total_onboarding_cost'];

    // Employee profile difficulty levels
    const DIFFICULTY_VERY_EASY = 'very_easy';
    const DIFFICULTY_EASY = 'easy';
    const DIFFICULTY_MEDIUM = 'medium';
    const DIFFICULTY_HARD = 'hard';
    const DIFFICULTY_VERY_HARD = 'very_hard';

    // Relations
    public function machines()
    {
        return $this->belongsToMany(Machine::class, 'machine_employee_profiles')
                   ->withPivot('required_count')
                   ->withTimestamps();
    }

    // Scopes
    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('recruitment_difficulty', $difficulty);
    }

    public function scopeWithinSalaryRange($query, $minSalary = null, $maxSalary = null)
    {
        if ($minSalary) {
            $query->where('monthly_avg_salary', '>=', $minSalary);
        }
        if ($maxSalary) {
            $query->where('monthly_avg_salary', '<=', $maxSalary);
        }
        return $query;
    }

    public function scopeEasyToRecruit($query)
    {
        return $query->whereIn('recruitment_difficulty', [self::DIFFICULTY_VERY_EASY, self::DIFFICULTY_EASY]);
    }

    public function scopeHardToRecruit($query)
    {
        return $query->whereIn('recruitment_difficulty', [self::DIFFICULTY_HARD, self::DIFFICULTY_VERY_HARD]);
    }

    // Accessors
    public function getDifficultyLevelAttribute()
    {
        return match($this->recruitment_difficulty) {
            self::DIFFICULTY_VERY_EASY => 'Very Easy',
            self::DIFFICULTY_EASY => 'Easy',
            self::DIFFICULTY_MEDIUM => 'Medium',
            self::DIFFICULTY_HARD => 'Hard',
            self::DIFFICULTY_VERY_HARD => 'Very Hard',
            default => $this->recruitment_difficulty
        };
    }

    public function getSalaryRangeAttribute()
    {
        return [
            'min' => $this->monthly_min_salary,
            'avg' => $this->monthly_avg_salary,
            'max' => $this->monthly_max_salary
        ];
    }

    public function getTotalOnboardingCostAttribute()
    {
        return $this->recruitment_cost_per_employee + $this->training_cost_per_employee;
    }
}
