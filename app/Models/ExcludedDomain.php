<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcludedDomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'domain',
        'expired_at',
        'status',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
