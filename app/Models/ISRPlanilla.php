<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ISRPlanilla extends Model
{
    protected $table = 'i_s_r_planillas';

    protected $fillable = [
        'sueldo_inicio',
        'sueldo_fin',
        'porcentaje',
        'tipo', // ISR  Vecinal
    ];
}
