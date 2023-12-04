<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    public function division()
    {
        return $this->hasMany(District::class);
    }
    public function divisions()
    {
        return $this->hasMany(District::class);
    }

    public function flag()
    {
        return $this->hasOne(FlagIcon::class,'title','iso2');
    }

    public function getFlagIconAttribute()
    {
        return $this->flag ? static_asset($this->flag->image) : static_asset('images/flags/ad.png');
    }
}
