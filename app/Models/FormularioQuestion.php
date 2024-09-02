<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormularioQuestion extends Model
{
    use HasFactory;
    protected $fillable = [
        'formulario_id',
        'question_id',
    ];
}
