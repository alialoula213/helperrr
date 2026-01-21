<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'message',
        'deposit_id',
        'withdrawal_id',
        'user_id',
        'response',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
