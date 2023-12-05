<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    public function district()
    {
        return $this->belongsTo(District::class);
    }
    
     public function division()
    {
        return $this->belongsTo(Division::class);
    }
    
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    
     public function upazila()
    {
        return $this->belongsTo(Upazila::class);
    }
    
}