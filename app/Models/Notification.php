<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    // Notification types
    const TYPE_FINANCE_FUNDS_CHANGED = 'finance_funds_changed';
    const TYPE_TECHNOLOGY_RESEARCH_STARTED = 'technology_research_started';
    const TYPE_TECHNOLOGY_RESEARCH_COMPLETED = 'technology_research_completed';
    const TYPE_PURCHASE_ORDERED = 'purchase_ordered';
    const TYPE_PURCHASE_DELIVERED = 'purchase_delivered';
    const TYPE_PURCHASE_CANCELLED = 'purchase_cancelled';

    // Icons for different notification types
    const ICONS = [
        self::TYPE_FINANCE_FUNDS_CHANGED => 'ki-filled ki-dollar',
        self::TYPE_TECHNOLOGY_RESEARCH_STARTED => 'ki-filled ki-technology-1',
        self::TYPE_TECHNOLOGY_RESEARCH_COMPLETED => 'ki-filled ki-technology-1',
        self::TYPE_PURCHASE_ORDERED => 'ki-filled ki-ship',
        self::TYPE_PURCHASE_DELIVERED => 'ki-filled ki-ship',
        self::TYPE_PURCHASE_CANCELLED => 'ki-filled ki-ship',
    ];

    // Methods
    public function markAsRead()
    {
        $this->update([
            'read_at' => now(),
        ]);
    }

    public function getIconAttribute($value)
    {
        return $value ?? self::ICONS[$this->type] ?? 'ki-filled ki-notification-status';
    }

    public function setIconAttribute($value)
    {
        $this->attributes['icon'] = $value ?? self::ICONS[$this->type] ?? 'ki-filled ki-notification-status';
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }
} 