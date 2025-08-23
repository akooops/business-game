<?php

namespace App\Models;

use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyTechnology extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'research_cost' => 'decimal:3',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $appends = ['is_researching', 'research_progress'];

    //Relations
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function technology()
    {
        return $this->belongsTo(Technology::class);
    }

    //Accessors
    public function getIsResearchingAttribute()
    {
        return $this->started_at && !$this->completed_at;
    }

    public function getResearchProgressAttribute()
    {
        if(!$this->is_researching) {
            return 100;
        }

        $currentTimestamp = SettingsService::getCurrentTimestamp();
        $startedAt = $this->started_at;
        $completedAt = $this->completed_at;

        if (!$startedAt || !$completedAt) {
            return 0;
        }

        $progress = $startedAt->diffInHours($currentTimestamp);
        $totalDays = $startedAt->diffInHours($completedAt);

        if($totalDays == 0){
            return 100;
        }

        return round(($progress / $totalDays) * 100, 2);
    }
}
