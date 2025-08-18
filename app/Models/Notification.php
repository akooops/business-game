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

    // Technology research
    const TYPE_TECHNOLOGY_RESEARCH_STARTED = 'technology_research_started';
    const TYPE_TECHNOLOGY_RESEARCH_COMPLETED = 'technology_research_completed';

    // Procurement
    const TYPE_PURCHASE_ORDERED = 'purchase_ordered';
    const TYPE_PURCHASE_DELIVERED = 'purchase_delivered';
    const TYPE_PURCHASE_CANCELLED = 'purchase_cancelled';

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

    // Employees
    const TYPE_EMPLOYEE_HIRED = 'employee_hired';
    const TYPE_EMPLOYEE_MOOD_DECREASED = 'employee_mood_decreased';
    const TYPE_EMPLOYEE_RESIGNED = 'employee_resigned';
    const TYPE_EMPLOYEE_SALARY_PAID = 'employee_salary_paid';

    // Machines
    const TYPE_MACHINE_SETUP = 'machine_setup';
    const TYPE_MACHINE_ASSIGNED_EMPLOYEE = 'machine_assigned_employee';
    const TYPE_MACHINE_PRODUCTION_STARTED = 'machine_production_started';
    const TYPE_MACHINE_PRODUCTION_COMPLETED = 'machine_production_completed';
    const TYPE_MACHINE_BROKEN = 'machine_broken';
    const TYPE_MACHINE_RELIABILITY_DECREASED = 'machine_reliability_decreased';
    const TYPE_MACHINE_OPERATION_COSTS_PAID = 'machine_operation_costs_paid';
    const TYPE_MACHINE_SOLD = 'machine_sold';

    // Maintenance
    const TYPE_MACHINE_MAINTENANCE_STARTED = 'machine_maintenance_started';
    const TYPE_MACHINE_MAINTENANCE_COMPLETED = 'machine_maintenance_completed';

    // Loans
    const TYPE_LOAN_BORROWED = 'loan_borrowed';
    const TYPE_LOAN_BORROWED_INSUFFICIENT_FUNDS = 'loan_borrowed_insufficient_funds';
    const TYPE_LOAN_PAID = 'loan_paid';

    // Advertisers
    const TYPE_AD_PACKAGE_CREATED = 'ad_package_created';
    const TYPE_AD_PACKAGE_COMPLETED = 'ad_package_completed';

    // Icons for different notification types
    const ICONS = [
        self::TYPE_TECHNOLOGY_RESEARCH_STARTED => 'fa-solid fa-rocket',
        self::TYPE_TECHNOLOGY_RESEARCH_COMPLETED => 'fa-solid fa-microchip',
        self::TYPE_PURCHASE_ORDERED => 'fa-solid fa-coins',
        self::TYPE_PURCHASE_DELIVERED => 'fa-solid fa-truck',
        self::TYPE_PURCHASE_CANCELLED => 'fa-solid fa-circle-xmark',
        self::TYPE_COUNTRIES_IMPORT_BLOCKED => 'ki-filled ki-ship',
        self::TYPE_COUNTRIES_IMPORT_ALLOWED => 'ki-filled ki-ship',
        self::TYPE_COUNTRIES_CUSTOMS_DUTIES_RATE_RAISED => 'ki-filled ki-ship',
        self::TYPE_COUNTRIES_CUSTOMS_DUTIES_RATE_LOWERED => 'ki-filled ki-ship',
        self::TYPE_OIL_PRICE_RAISED => 'ki-filled ki-ship',
        self::TYPE_OIL_PRICE_LOWERED => 'ki-filled ki-ship',
        self::TYPE_SUEZ_CANAL_CLOSED => 'ki-filled ki-ship',
        self::TYPE_SUEZ_CANAL_OPENED => 'ki-filled ki-ship',
        self::TYPE_INVENTORY_EXPIRED => 'fa-solid fa-clock',
        self::TYPE_INVENTORY_DAMAGED => 'fa-solid circle-exclamation',
        self::TYPE_INVENTORY_COSTS_PAID => 'fa-solid fa-coins',
        self::TYPE_SALE_INITIATED => 'fa-solid fa-cart-shopping',
        self::TYPE_SALE_DELIVERED => 'fa-solid fa-truck',
        self::TYPE_SALE_CANCELLED => 'fa-solid fa-circle-xmark',
        self::TYPE_EMPLOYEE_HIRED => 'fa-solid fa-user-plus',
        self::TYPE_EMPLOYEE_MOOD_DECREASED => 'fa-solid fa-face-frown',
        self::TYPE_EMPLOYEE_RESIGNED => 'fa-solid fa-user-xmark',
        self::TYPE_EMPLOYEE_SALARY_PAID => 'fa-solid fa-coins',
        self::TYPE_MACHINE_SETUP => 'fa-solid fa-gear',
        self::TYPE_MACHINE_ASSIGNED_EMPLOYEE => 'fa-solid fa-user-plus',
        self::TYPE_MACHINE_PRODUCTION_STARTED => 'fa-solid fa-gears',
        self::TYPE_MACHINE_PRODUCTION_COMPLETED => 'fa-solid fa-gears',
        self::TYPE_MACHINE_BROKEN => 'fa-solid fa-screwdriver-wrench',
        self::TYPE_MACHINE_RELIABILITY_DECREASED => 'fa-solid fa-screwdriver-wrench',
        self::TYPE_MACHINE_MAINTENANCE_STARTED => 'fa-solid fa-hammer',
        self::TYPE_MACHINE_OPERATION_COSTS_PAID => 'fa-solid fa-coins',
        self::TYPE_MACHINE_MAINTENANCE_COMPLETED => 'fa-solid fa-hammer',
        self::TYPE_MACHINE_SOLD => 'fa-solid fa-coins',
        self::TYPE_LOAN_BORROWED => 'fa-solid fa-coins',
        self::TYPE_LOAN_BORROWED_INSUFFICIENT_FUNDS => 'fa-solid fa-coins',
        self::TYPE_LOAN_PAID => 'fa-solid fa-coins',
        self::TYPE_AD_PACKAGE_CREATED => 'fa-solid fa-ad',
        self::TYPE_AD_PACKAGE_COMPLETED => 'fa-solid fa-ad',
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