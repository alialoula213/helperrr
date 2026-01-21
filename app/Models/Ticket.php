<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'admin_id', 'priority_id', 'status_id', 'category_id', 'read', 'admin_read', 'ticket_id', 'title', 'message'
    ];

    public function category()
    {
        return $this->belongsTo(TicketCategory::class);
    }

    public function priority()
    {
        return $this->belongsTo(TicketPriority::class);
    }

    public function status()
    {
        return $this->belongsTo(TicketStatus::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }
}
