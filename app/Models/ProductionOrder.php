<?php

namespace App\Models;

use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:3',
        'started_at' => 'datetime',
        'estimated_completed_at' => 'datetime',
        'real_completed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $appends = ['is_producing', 'producing_progress'];

    // Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Relations
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function companyMachine()
    {
        return $this->belongsTo(CompanyMachine::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Accessors
    public function getIsProducingAttribute()
    {
        return $this->started_at && !$this->completed_at;
    }

    public function getProducingProgressAttribute()
    {
        if(!$this->is_producing) {
            return 100;
        }

        $currentTimestamp = SettingsService::getCurrentTimestamp();
        $startedAt = $this->started_at;
        $realCompletedAt = $this->real_completed_at;

        if (!$startedAt || !$realCompletedAt) {
            return 0;
        }

        $progress = $startedAt->diffInHours($currentTimestamp);
        $totalDays = $startedAt->diffInHours($realCompletedAt);

        if($totalDays == 0){
            return 100;
        }

        return round(($progress / $totalDays) * 100, 2);
    }
}
