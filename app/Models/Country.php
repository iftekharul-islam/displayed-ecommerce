<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    public function divisions()
    {
        return $this->hasMany(Division::class);
    }
    public function districts()
    {
        return $this->hasMany(District::class);
    }
    
    public function upazilas()
    {
        return $this->hasMany(Upazila::class);
    }
    
    public function areas()
    {
        return $this->hasMany(Area::class);
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
