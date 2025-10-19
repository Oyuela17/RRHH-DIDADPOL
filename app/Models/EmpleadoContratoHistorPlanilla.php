<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoContratoHistorial extends Model
{
    protected $table = 'empleados_contratos_histor'; // Nombre exacto de la tabla en PostgreSQL

    protected $primaryKey = 'cod_contrato'; // Llave primaria

    public $timestamps = false; // Si no usas created_at y updated_at

    protected $fillable = [
        'cod_empleado',
        'cod_tipo_empleado',
        'cod_puesto',
        'fecha_inicio_contrato',
        'fecha_final_contrato',
        'salario',
        'contrato_activo',
        'observaciones',
        'usr_registro',
        'fec_registro',
        'usr_modificacion',
        'fec_modificacion',
        'cod_terminacion_contrato',
    ];
    
}
