<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use App\Traits\HasFiles;

class CompanyMachine extends Model
{
    use HasFactory, HasFiles;

    protected $guarded = ['id'];

    protected $casts = [
        'current_reliability' => 'decimal:3',
        'setup_at' => 'datetime',
        'last_maintenance_at' => 'datetime',
        'last_broken_at' => 'datetime',
    ];

    const STATUS_SETUP = 'setup';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_BROKEN = 'broken';
    const STATUS_MAINTENANCE = 'maintenance';

    // Relations
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
