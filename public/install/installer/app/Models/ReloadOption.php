<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReloadOption extends Model
{
    use HasFactory;
    
    protected $fillable = ['option_id','telecom','facevalue', 'value','description','type'];
}
