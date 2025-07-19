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
    // Finance
    const TYPE_FINANCE_FUNDS_CHANGED = 'finance_funds_changed';

    // Technology research
    const TYPE_TECHNOLOGY_RESEARCH_STARTED = 'technology_research_started';
    const TYPE_TECHNOLOGY_RESEARCH_COMPLETED = 'technology_research_completed';

    // Procurement
    const TYPE_PURCHASE_ORDERED = 'purchase_ordered';
    const TYPE_PURCHASE_DELIVERED = 'purchase_delivered';
    const TYPE_PURCHASE_CANCELLED = 'purchase_cancelled';
    const TYPE_PURCHASE_DELIVERY_DELAYED = 'purchase_delivery_delayed';

    // Countries import
    const TYPE_COUNTRIES_IMPORT_BLOCKED = 'countries_import_blocked';
    const TYPE_COUNTRIES_IMPORT_ALLOWED = 'countries_import_allowed';

    // Countries customs duties rate
    const TYPE_COUNTRIES_CUSTOMS_DUTIES_RATE_RAISED = 'countries_customs_duties_rate_raised';
    const TYPE_COUNTRIES_CUSTOMS_DUTIES_RATE_LOWERED = 'countries_customs_duties_rate_lowered';

    // Oil price
    const TYPE_OIL_PRICE_RAISED = 'oil_price_raised';
    const TYPE_OIL_PRICE_LOWERED = 'oil_price_lowered';

    // Suez canal
    const TYPE_SUEZ_CANAL_CLOSED = 'suez_canal_closed';
    const TYPE_SUEZ_CANAL_OPENED = 'suez_canal_opened';

    // Inventory expired
    const TYPE_INVENTORY_EXPIRED = 'inventory_expired';

    // Inventory damaged
    const TYPE_INVENTORY_DAMAGED = 'inventory_damaged';

    // Inventory costs
    const TYPE_INVENTORY_COSTS_PAID = 'inventory_costs_paid';

    // Sales
    const TYPE_SALE_INITIATED = 'sale_initiated';
    const TYPE_SALE_DELIVERED = 'sale_delivered';
    const TYPE_SALE_CANCELLED = 'sale_cancelled';
    const TYPE_SALE_DELIVERY_DELAYED = 'sale_delivery_delayed';



    // Icons for different notification types
    const ICONS = [
        self::TYPE_FINANCE_FUNDS_CHANGED => 'ki-filled ki-dollar',
        self::TYPE_TECHNOLOGY_RESEARCH_STARTED => 'ki-filled ki-technology-1',
        self::TYPE_TECHNOLOGY_RESEARCH_COMPLETED => 'ki-filled ki-technology-1',
        self::TYPE_PURCHASE_ORDERED => 'ki-filled ki-ship',
        self::TYPE_PURCHASE_DELIVERED => 'ki-filled ki-ship',
        self::TYPE_PURCHASE_CANCELLED => 'ki-filled ki-ship',
        self::TYPE_PURCHASE_DELIVERY_DELAYED => 'ki-filled ki-ship',
        self::TYPE_COUNTRIES_IMPORT_BLOCKED => 'ki-filled ki-ship',
        self::TYPE_COUNTRIES_IMPORT_ALLOWED => 'ki-filled ki-ship',
        self::TYPE_COUNTRIES_CUSTOMS_DUTIES_RATE_RAISED => 'ki-filled ki-ship',
        self::TYPE_COUNTRIES_CUSTOMS_DUTIES_RATE_LOWERED => 'ki-filled ki-ship',
        self::TYPE_OIL_PRICE_RAISED => 'ki-filled ki-ship',
        self::TYPE_OIL_PRICE_LOWERED => 'ki-filled ki-ship',
        self::TYPE_SUEZ_CANAL_CLOSED => 'ki-filled ki-ship',
        self::TYPE_SUEZ_CANAL_OPENED => 'ki-filled ki-ship',
        self::TYPE_INVENTORY_EXPIRED => 'ki-filled ki-dropbox',
        self::TYPE_INVENTORY_DAMAGED => 'ki-filled ki-dropbox',
        self::TYPE_INVENTORY_COSTS_PAID => 'ki-filled ki-dropbox',
        self::TYPE_SALE_INITIATED => 'ki-filled ki-ship',
        self::TYPE_SALE_DELIVERED => 'ki-filled ki-ship',
        self::TYPE_SALE_CANCELLED => 'ki-filled ki-ship',
        self::TYPE_SALE_DELIVERY_DELAYED => 'ki-filled ki-ship',
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