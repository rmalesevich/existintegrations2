<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExistUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'username',
        'timezone',
        'access_token',
        'refresh_token',
        'token_expires'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
