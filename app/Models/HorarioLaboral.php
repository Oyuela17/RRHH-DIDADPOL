<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioLaboral extends Model
{
    protected $table = 'horarios_laborales';
    protected $primaryKey = 'cod_horario';
    public $timestamps = false;

    protected $fillable = [
        'nom_horario',
        'hora_inicio',
        'hora_final',
        'dias_semana',
        'usr_registro',
        'fec_registro',
        'usr_modificacion',
        'fec_modificacion'
    ];

    protected $casts = [
        'dias_semana' => 'array',
        'fec_registro' => 'datetime',
        'fec_modificacion' => 'datetime',
    ];
}
