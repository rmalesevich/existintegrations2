<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Retreive the ExistUser object associated with this User
     * 
     * @return ExistUser
     */
    public function existUser()
    {
        return $this->hasOne(ExistUser::class);
    }

    /**
     * Retrieve the WhatPulseUser object associated with this User
     * 
     * @return WhatPulseUser
     */
    public function whatPulseUser()
    {
        return $this->hasOne(WhatPulseUser::class);
    }
}
