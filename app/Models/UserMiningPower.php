<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMiningPower extends Model
{
    use HasFactory;

    protected $fillable = [
        'power',
        'user_id',
        'status',
        'expire_date',
        'last_sum',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
