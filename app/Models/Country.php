<?php

namespace App\Models;

use App\Models\File;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Country extends Model
{
    use HasFactory, HasFiles;

    protected $guarded = ['id'];

    protected $casts = [
        'customs_duties_rate' => 'decimal:3',
        'allows_imports' => 'boolean',
    ];

    //Relations
    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }
}
