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
     * Returns if the passed in integration is enabled for this User
     * 
     * @param string $integration
     * @return bool
     */
    public function integrationEnabled(string $integration): bool
    {
        switch ($integration) {
            case "whatpulse":
                return ($this->whatPulseUser !== null);
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * Retrieve the WhatPulseUser object associated with this User
     * 
     * @return WhatPulseUser
     */
    public function whatpulseUser()
    {
        return $this->hasOne(WhatPulseUser::class);
    }

    /**
     * Returns all attributes linked to this User
     * 
     * @return UserAttribute
     */
    public function attributes()
    {
        return $this->hasMany(UserAttribute::class);
    }
}
