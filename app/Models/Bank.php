<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFiles;
use Illuminate\Support\Facades\URL;

class Bank extends Model
{
    use HasFactory, HasFiles;

    protected $guarded = ['id'];

    protected $appends = ['logo_url'];

    protected $casts = [
        'loan_interest_rate' => 'decimal:3',
        'loan_max_amount' => 'decimal:3',
    ];

    // Relations
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function logo()
    {
        return $this->morphOne(File::class, 'model')->where('is_main', 1);
    }

    // Accessors
    public function getLogoUrlAttribute()
    {
        return ($this->logo) ? $this->logo->url : URL::to('assets/images/default-bank-logo.jpg');
    }
}
