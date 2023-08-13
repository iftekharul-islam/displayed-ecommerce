<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitorCountByCountry extends Model
{
    use HasFactory;

    protected $fillable = [
        'short_url_id',
        'country',
        'visited_at',
        'total_count',
    ];
}
