<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortUrlType extends Model
{
    use HasFactory;

    protected $fillable  = [
        'name',
        'redirect_url',
        'isDefault'
    ];

    protected $appends = ['count'];

    public function urls()
    {
        return $this->hasMany(ShortUrl::class, 'type_id', 'id');
    }

    public function getCountAttribute()
    {
        return $this->urls->count();
    }
}
