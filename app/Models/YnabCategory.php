<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YnabCategory extends Model
{
    use HasFactory;

    protected $table = 'ynab_categories';

    protected $fillable = [
        'user_id',
        'category_group_name',
        'category_id',
        'category_name',
        'deleted_flag',
        'included_flag'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
