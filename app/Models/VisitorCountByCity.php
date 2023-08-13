<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitorCountByCity extends Model
{
    use HasFactory;

    protected $fillable = [
        'short_url_id',
        'city',
        'visited_at',
        'total_count',
    ];
}
