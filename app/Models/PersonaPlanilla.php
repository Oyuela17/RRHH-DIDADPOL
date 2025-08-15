<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonaPlanilla extends Model
{
    protected $table = 'personas';
    protected $primaryKey = 'cod_persona';
    public $timestamps = false;

    protected $fillable = [
        'genero',
        'estado_civil',
        'nombre_completo',
        'fec_nacimiento',
        'lugar_nacimiento',
        'nacionalidad',
        'dni',
        'foto_persona',
        'fec_registro',
        'fec_modificacion',
        'usr_modificacion',
        'usr_registro',
        'rtn',
    ];

    protected $casts = [
        'fec_nacimiento'   => 'date',
        'fec_registro'     => 'datetime',
        'fec_modificacion' => 'datetime',
    ];

    // Relación con empleado
    public function empleado()
    {
        return $this->hasOne(\App\Models\EmpleadoPlanilla::class, 'cod_persona', 'cod_persona');
    }
}
