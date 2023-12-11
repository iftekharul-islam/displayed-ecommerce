<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = ['name','address','email','phone_no','default_shipping','default_billing','country','division','district', 'upazila','area','longitude','latitude','postal_code','user_id','address_ids'];

    protected $casts = [
        'address_ids' => 'array',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
