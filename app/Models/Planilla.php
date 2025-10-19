<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planilla extends Model
{
    protected $table = 'planillas';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'cod_persona',
        'dd',
        'dt',
        'salario_bruto',
        'ihss',
        'isr',
        'injupemp',
        'impuesto_vecinal',
        'dias_descargados',

        'injupemp_reingresos',
        'injupemp_prestamos',
        'prestamo_banco_atlantida',
        'pagos_deducibles',
        'colegio_admon_empresas',
        'cuota_coop_elga',
        'total_deducciones',
        'total_a_pagar',
        'creado_en',
    ];

    protected $casts = [
        'cod_persona'               => 'integer',
        'dd'                        => 'integer',
        'dt'                        => 'integer',
        'salario_bruto'             => 'float',
        'ihss'                      => 'float',
        'isr'                       => 'float',
        'injupemp'                  => 'float',
        'impuesto_vecinal'          => 'float',
        'dias_descargados'          => 'integer',
        'injupemp_reingresos'       => 'float',
        'injupemp_prestamos'        => 'float',
        'prestamo_banco_atlantida'  => 'float',
        'pagos_deducibles'          => 'float',
        'colegio_admon_empresas'    => 'float',
        'cuota_coop_elga'           => 'float',
        'total_deducciones'         => 'float',
        'total_a_pagar'             => 'float',
        'creado_en'                 => 'datetime',
    ];
}
