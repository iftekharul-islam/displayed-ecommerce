<?php

namespace App\Models;

use App\Traits\CreatedBy;
use App\Traits\UpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tld extends Model
{
    use HasFactory;
    use CreatedBy;
    use UpdatedBy;

    protected $fillable = [
        'campaign_id',
        'name',
        'price',
        'created_by',
        'updated_by',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }
}
