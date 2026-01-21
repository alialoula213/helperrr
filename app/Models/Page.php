<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'content', 'status', 'type', 'meta_keywords', 'meta_description'
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
