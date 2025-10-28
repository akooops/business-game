<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\CalculationsService;

class Wilaya extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    //Relations
    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }
} 