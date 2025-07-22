<?php

namespace App\Models;

use App\Models\File;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasFiles;
    
    //Properties
    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $appends = ['fullname', 'avatarUrl'];

    //Relationships
    public function file()
    {
        return $this->morphOne(File::class, 'model');
    }

    public function company()
    {
        return $this->hasOne(Company::class);
    }
    
    //Accessors
    public function getFullnameAttribute()
    {
        return "{$this->firstname} {$this->lastname}";
    }

    public function getAvatarUrlAttribute()
    {
        // If user has a profile image, return it
        return ($this->file) ? $this->file->url : URL::to('assets/images/default-avatar.jpg');
    }
}   
