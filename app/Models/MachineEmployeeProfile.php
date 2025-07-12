<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MachineEmployeeProfile extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relations
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class);
    }

    // Accessors
}
