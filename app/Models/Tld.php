<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tld extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'name',
        'price',
        'last_updated_at',
    ];
}
