<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'funds' => 'decimal:3',
        'carbon_footprint' => 'decimal:3',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
}
