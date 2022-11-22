<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserData extends Model
{
    use HasFactory;

    protected $table = 'user_data';

    protected $fillable = [
        'user_id',
        'service',
        'service_id',
        'attribute',
        'date_id',
        'value',
        'sent_to_exist',
        'response_date',
        'response'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
