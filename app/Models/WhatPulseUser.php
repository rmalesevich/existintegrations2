<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatPulseUser extends Model
{
    use HasFactory;

    protected $table = 'whatpulse_users';

    protected $fillable = [
        'user_id',
        'account_name',
        'last_pulse'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
