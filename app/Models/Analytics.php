<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Analytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'short_url_id',
        'operating_system',
        'operating_system_version',
        'browser',
        'browser_version',
        'device_type',
        'ip_address',
    ];
}
