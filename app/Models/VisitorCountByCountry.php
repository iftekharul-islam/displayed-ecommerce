<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorCountByCountry extends Model
{
    use HasFactory;

    protected $fillable = [
        'short_url_id',
        'country',
        'visit_date',
        'total_count',
    ];

    public function shortUrl(): BelongsTo
    {
        return $this->belongsTo(ShortUrl::class, 'short_url_id', 'id');
    }

    public function visitorCountByCities(): HasMany
    {
        return $this->hasMany(VisitorCountByCity::class, 'visitor_count_by_country_id', 'id');
    }
}
