<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'wallet',
        'email',
        'password',
        'balance',
        'status',
        'banned_message',
        'allow_withdrawal',
        'ref_id',
        'ref_hits',
        'ip',
        'comments',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /*
     * Auto hash password when create/update
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Hash::needsRehash($value) ? \Hash::make($value) : $value;
    }

    public function hashpower()
    {
        return $this->hasMany(UserMiningPower::class)->whereStatus('active')->sum('power');
    }

    public function miningplans()
    {
        return $this->hasMany(UserMiningPower::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function logs()
    {
        return $this->hasMany(UserLog::class);
    }
}
