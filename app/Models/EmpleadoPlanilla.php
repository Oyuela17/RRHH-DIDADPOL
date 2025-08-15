<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpleadoPlanilla extends Model
{
    protected $table = 'empleados';
    protected $primaryKey = 'cod_empleado';
    public $timestamps = false;

    protected $fillable = [
        'cod_persona',
        'cod_tipo_modal',
        'cod_puesto',
        'cod_oficina',
        'cod_nivel_educativo',
        'cod_horario',
        'es_jefe',
        'fecha_contratacion',
        'fecha_notificacion',
        'cod_tipo_terminacion',
        'email_trabajo',
        'fec_registro',
        'usr_registro',
        'fec_modificacion',
        'usr_modificacion',
        'cod_tipo_empleado',
    ];

    protected $casts = [
        'es_jefe'             => 'boolean',
        'fecha_contratacion'  => 'date',
        'fecha_notificacion'  => 'date',
        'fec_registro'        => 'datetime',
        'fec_modificacion'    => 'datetime',
    ];

    // Relaciones recomendadas
    public function persona()
    {
        return $this->belongsTo(PersonaPlanilla::class, 'cod_persona', 'cod_persona');
    }

    public function puesto()
    {
        return $this->belongsTo(PuestoPlanilla::class, 'cod_puesto', 'cod_puesto');
    }

    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'cod_oficina', 'cod_oficina');
    }

    public function nivelEducativo()
    {
        return $this->belongsTo(NivelEducativo::class, 'cod_nivel_educativo', 'cod_nivel_educativo');
    }

    public function horario()
    {
        return $this->belongsTo(HorarioLaboral::class, 'cod_horario', 'cod_horario');
    }

    public function contrato()
    {
        return $this->hasOne(EmpleadoContratoHistorPlanilla::class, 'cod_empleado', 'cod_empleado')
                    ->where('contrato_activo', true);
    }

    
    public function asistencias()
{
    return $this->hasMany(\App\Models\ControlAsistencia::class, 'cod_empleado', 'cod_empleado');
}

}
