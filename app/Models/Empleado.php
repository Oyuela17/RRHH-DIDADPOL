<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $table = 'empleados';
    protected $primaryKey = 'cod_empleado';
    public $incrementing = false; // porque no es ID autoincremental
    protected $keyType = 'string'; // o 'int' si es numérico

    protected $fillable = [
        'cod_empleado', 'cod_puesto', 'fecha_contratacion', 'salario', 'email_trabajo', 'rtn'
        // agrega más si los vas a usar
    ];
}
