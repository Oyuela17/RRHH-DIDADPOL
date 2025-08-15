<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PuestoPlanilla extends Model
{
    protected $table = 'puestos';
    protected $primaryKey = 'cod_puesto';
    public $timestamps = false;

    protected $fillable = [
        'nom_puesto',
        'fec_registro',
        'usr_registro',
        'cod_fuente_final',
        'funciones_puest',
        'sueldo_base',
    ];

    protected $casts = [
        'fec_registro'  => 'datetime',
        'sueldo_base'   => 'float',
    ];

    // Relación con empleados
    public function empleados()
    {
        return $this->hasMany(\App\Models\EmpleadoPlanilla::class, 'cod_puesto', 'cod_puesto');
    }
}















