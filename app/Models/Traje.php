<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Traje extends Model
{
    use HasFactory;
    protected $fillable = [
        'description',
        'active',
    ];
}
