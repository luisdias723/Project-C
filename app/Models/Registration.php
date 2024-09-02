<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'status_id',
        'formulario_id',
        'active',
        'qrCode',
        'ticket',
        'email',
        'edit',
        'type'
    ];
}
