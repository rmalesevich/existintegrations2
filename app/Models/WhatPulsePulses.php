<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatPulsePulses extends Model
{
    use HasFactory;

    protected $table = 'whatpulse_pulses';

    protected $fillable = [
        'user_id',
        'pulse_id',
        'date_id',
        'pulse_date',
        'keystrokes',
        'mouse_clicks',
        'download_mb',
        'upload_mb',
        'uptime_minutes',
        'sent_to_exist'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
