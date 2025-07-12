<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFiles;

class EmployeeProfile extends Model
{
    use HasFactory, HasFiles;

    protected $guarded = ['id'];

    protected $appends = ['recruitment_difficulty_display'];

    protected $casts = [
        'skills' => 'array',
        'min_salary_month' => 'decimal:3',
        'avg_salary_month' => 'decimal:3',
        'max_salary_month' => 'decimal:3',
        'recruitment_cost_per_employee' => 'decimal:3',
        'training_cost_per_employee' => 'decimal:3',
    ];

    // Recruitment difficulties
    const RECRUITMENT_DIFFICULTY_VERY_EASY = 'very_easy';
    const RECRUITMENT_DIFFICULTY_EASY = 'easy';
    const RECRUITMENT_DIFFICULTY_MEDIUM = 'medium';
    const RECRUITMENT_DIFFICULTY_HARD = 'hard';
    const RECRUITMENT_DIFFICULTY_VERY_HARD = 'very_hard';

    //Relations
    
    //Scopes
    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('recruitment_difficulty', $difficulty);
    }

    //Accessors
    public function getRecruitmentDifficultyDisplayAttribute()
    {
        return match($this->recruitment_difficulty) {
            self::RECRUITMENT_DIFFICULTY_VERY_EASY => 'Very Easy',
            self::RECRUITMENT_DIFFICULTY_EASY => 'Easy',
            self::RECRUITMENT_DIFFICULTY_MEDIUM => 'Medium',
            self::RECRUITMENT_DIFFICULTY_HARD => 'Hard',
            self::RECRUITMENT_DIFFICULTY_VERY_HARD => 'Very Hard',
            default => 'Unknown'
        };
    }
}
