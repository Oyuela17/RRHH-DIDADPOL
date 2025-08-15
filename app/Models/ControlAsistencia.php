<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlAsistencia extends Model
{
    protected $table = 'control_asistencia';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'cod_empleado',
        'fecha',
        'hora_entrada',
        'hora_salida',
        'tipo_registro',
        'observacion',
        'creado_en',
    ];

    protected $casts = [
        'fecha'        => 'date',
       // 'hora_entrada' => 'datetime:H:i:s',
       // 'hora_salida'  => 'datetime:H:i:s',
        'creado_en'    => 'datetime',
    ];

    public function empleado()
    {
        return $this->belongsTo(\App\Models\EmpleadoPlanilla::class, 'cod_empleado', 'cod_empleado');
    }
    
}