<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status'
    ];

    public function districts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(District::class,'shipping_class_id');
    }
}
