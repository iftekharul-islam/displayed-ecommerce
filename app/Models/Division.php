<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function district()
    {
        return $this->hasMany(district::class);
    }
    
    public function upazilas()
    {
        return $this->hasMany(Upazila::class);
    }
    
    public function areas()
    {
        return $this->hasMany(Area::class);
    }
    
}
