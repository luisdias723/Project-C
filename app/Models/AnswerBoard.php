<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnswerBoard extends Model
{
    use HasFactory;
    protected $fillable = [
        'board_id',
        'answer_id',
    ];
}
