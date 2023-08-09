<?php

namespace App\Models;

use App\Models\Tld;
use App\Models\Campaign;
use App\Traits\CreatedBy;
use App\Traits\DeletedBy;
use App\Traits\UpdatedBy;
use App\Models\VisitorCount;
use App\Models\VisitorCountByCountry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShortUrl extends Model
{
    use HasFactory;
    use CreatedBy;
    use UpdatedBy;
    use DeletedBy;
    use SoftDeletes;

    protected $fillable = [
        'tld_id',
        'campaign_id',
        'original_domain',
        'destination_domain',
        'short_url',
        'su_tld_name',
        'su_tld_price',
        'url_key',
        'expired_at',
        'auto_renewal',
        'status',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'auto_renewal' => 'boolean',
        'status' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }

    public function tld(): BelongsTo
    {
        return $this->belongsTo(Tld::class, 'tld_id', 'id');
    }

    public function visitorCount(): HasMany
    {
        return $this->hasMany(VisitorCount::class, 'short_url_id', 'id');
    }

    public function visitorCountByCountries(): HasMany
    {
        return $this->hasMany(VisitorCountByCountry::class, 'short_url_id', 'id');
    }
}
