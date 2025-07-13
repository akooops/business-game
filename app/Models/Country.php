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

    protected $appends = ['flag_url'];

    protected $casts = [
        'customs_duties_rate' => 'decimal:3',
        'allows_imports' => 'boolean',
    ];

    //Relations
    public function flag()
    {
        return $this->morphOne(File::class, 'model')->where('is_main', 1);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    // Methods
    public function getFlagUrlAttribute()
    {
        return ($this->flag) ? $this->flag->url : URL::to('assets/images/default-country-flag.jpg');
    }
}
