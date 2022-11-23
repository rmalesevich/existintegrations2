<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TraktUser extends Model
{
    use HasFactory;

    protected $table = 'trakt_users';

    protected $fillable = [
        'user_id',
        'username',
        'access_token',
        'refresh_token',
        'token_expires',
        'is_new'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUserAttribute($user)
    {
        return $this->username;
    }
}
