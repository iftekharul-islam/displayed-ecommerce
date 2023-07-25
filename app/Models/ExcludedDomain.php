<?php

namespace App\Models;

use App\Traits\CreatedBy;
use App\Traits\DeletedBy;
use App\Traits\UpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExcludedDomain extends Model
{
    use HasFactory;
    use CreatedBy;
    use UpdatedBy;
    use DeletedBy;
    use SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'domain',
        'expired_at',
        'auto_renewal',
        'status',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
}
