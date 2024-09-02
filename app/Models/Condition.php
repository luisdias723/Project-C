<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    use HasFactory;
    protected $fillable = [
        'question_id',
        'answer_id',
        'rule',
        'select_condition',
        'type_question',
        'formulario_id'
    ];
}
