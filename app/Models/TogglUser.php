<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TogglUser extends Model
{
    use HasFactory;

    protected $table = 'toggl_users';

    protected $fillable = [
        'user_id',
        'api_token',
        'external_user_id',
        'external_workspace_id',
        'is_new'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUserAttribute($user)
    {
        return $this->external_user_id;
    }
}
