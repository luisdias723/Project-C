<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $fillable = [
        'description',
        'type_question',
        'isMultiple',
        'isSpecial',
        'isSpecialBoards',
        'isTraje',
        'numDigits',
        'active',
    ];
}
