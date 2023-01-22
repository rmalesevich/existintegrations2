<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TogglProject extends Model
{
    use HasFactory;

    protected $table = 'toggl_projects';

    protected $fillable = [
        'user_id',
        'project_id',
        'project_name',
        'active_flag',
        'deleted_flag',
        'attribute'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
