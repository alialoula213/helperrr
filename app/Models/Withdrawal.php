<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'fees',
        'paid_amount',
        'tx_id',
        'status',
        'cancel_reason',
        'response',
        'comments',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
