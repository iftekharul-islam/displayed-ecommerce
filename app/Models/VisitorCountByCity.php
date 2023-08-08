<?php

namespace App\Models;

use App\Models\VisitorCountByCountry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitorCountByCity extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitor_count_by_country_id',
        'city',
        'visit_date',
        'total_count',
    ];

    public function visitorCountByCountry(): BelongsTo
    {
        return $this->belongsTo(VisitorCountByCountry::class, 'visitor_count_by_country_id', 'id');
    }
}
