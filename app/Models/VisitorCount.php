<?php

namespace App\Models;

use App\Models\ShortUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VisitorCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'short_url_id',
        'visit_date',
        'total_count',
    ];

    public function shortUrl(): BelongsTo
    {
        return $this->belongsTo(ShortUrl::class, 'short_url_id', 'id');
    }
}
