<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function permissions(): HasMany
    {
        $this->permissionClass = app(PermissionRegistrar::class)->getPermissionClass();

        return $this->hasMany($this->permissionClass);
    }
}
