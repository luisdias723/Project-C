<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quadro extends Model
{
    use HasFactory;
    protected $fillable = [
        'description',
        'active',
        'insc_limit',
        'total_insc',
    ];
}
