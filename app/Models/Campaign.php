<?php

namespace App\Models;

use App\Models\Tld;
use App\Models\ShortUrl;
use App\Traits\CreatedBy;
use App\Traits\DeletedBy;
use App\Traits\UpdatedBy;
use App\Models\ExcludedDomain;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory;
    use CreatedBy;
    use UpdatedBy;
    use DeletedBy;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'is_active',
        'last_updated_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function tlds(): HasMany
    {
        return $this->hasMany(Tld::class, 'campaign_id', 'id');
    }

    public function shortUrls(): HasMany
    {
        return $this->hasMany(ShortUrl::class, 'campaign_id', 'id');
    }

    public function excludedDomains(): HasMany
    {
        return $this->hasMany(ExcludedDomain::class, 'campaign_id', 'id');
    }
}
