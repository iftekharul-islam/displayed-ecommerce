<?php

namespace App\Models;

use App\Traits\CreatedBy;
use App\Traits\DeletedBy;
use App\Traits\UpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tld extends Model
{
    use HasFactory;
    use CreatedBy;
    use UpdatedBy;
    use DeletedBy;
    use SoftDeletes;

    protected $fillable = [
        'campaign_id',
        'name',
        'price',
        'last_updated_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }
}
