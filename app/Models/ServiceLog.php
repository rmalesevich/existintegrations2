<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceLog extends Model
{
    use HasFactory;

    protected $table = 'service_logs';

    protected $fillable = [
        'user_id',
        'service',
        'unauthorized',
        'message'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
