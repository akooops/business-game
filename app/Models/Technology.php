<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use App\Traits\HasFiles;

class Technology extends Model
{
    use HasFactory, HasFiles;

    protected $guarded = ['id'];

    protected $appends = ['image_url', 'is_researched'];

    protected $casts = [
        'research_cost' => 'decimal:3',
    ];

    //Relations
    public function image()
    {
        return $this->morphOne(File::class, 'model')->where('is_main', 1);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    //Accessors
    public function getImageUrlAttribute()
    {
        return ($this->image) ? $this->image->url : URL::to('assets/images/default-technology-image.jpg');
    }

    public function getIsResearchedAttribute()
    {
        if(!auth()->user()->company) return false;

        $companyTechnology = CompanyTechnology::where([
            'company_id' => auth()->user()->company->id, 
            'technology_id' => $this->id
        ])->exists();

        return $companyTechnology;
    }
}
