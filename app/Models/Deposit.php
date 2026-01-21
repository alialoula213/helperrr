<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice',
        'invoice_expire_date',
        'power',
        'amount',
        'paid_amount',
        'tx_id',
        'status',
        'cancel_reason',
        'response',
        'qrcode',
        'comments',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
