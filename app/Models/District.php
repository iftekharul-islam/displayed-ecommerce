<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
    public function country()
    {
        return $this->belongsTo(Country::class);
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
