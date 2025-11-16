@extends('layouts.dashboard')
@section('title', 'Gestión de Empleados')

@section('content')

@php
    // Permisos para el módulo EMPLEADOS
    $accionesEmpleados = $accionesPermitidas['EMPLEADOS'] ?? [
        'crear'      => false,
        'actualizar' => false,
        'eliminar'   => false,
    ];
@endphp

@if (session('success'))
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      Swal.fire({
        icon: 'success',
        title: 'Gestión de Empleados',
        text: '{{ session("success") }}',
        confirmButtonText: 'OK',
        confirmButtonColor: '#007bff'
      });
    });
  </script>
@endif

{{-- ESTILOS PARA ERRORES DENTRO DE LOS CAMPOS --}}
<style>
  .campo-grupo {
    position: relative;
    width: 100%;
  }

  .campo-grupo input,
  .campo-grupo select {
    width: 100%;
  }

  .error-badge {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 11px;
    color: #b00020;
    background: #ffe6e6;
    padding: 2px 6px;
    border-radius: 10px;
    pointer-events: none;
    max-width: 45%;
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
    display: none;
  }

  /* Para que el icono del calendario no se tape */
  .campo-grupo input[type="date"] {
    padding-right: 110px; /* espacio para badge + icono */
  }

  .campo-grupo input[type="date"] + .error-badge {
    right: 35px; /* deja espacio al icono del calendario */
  }

  input.error,
  select.error {
    background-color: #ffe6e6;
    border-color: #e53935;
  }
</style>

<div class="empleados-wrapper">
  <div class="titulo-con-linea">
    <h2>Gestión de Empleados</h2>
  </div>

  <div class="acciones-superiores">
    <div class="lado-izquierdo">
      <form method="GET" action="{{ route('empleados.index') }}" class="form-busqueda" onsubmit="return validarBusqueda()">
        <input type="text" name="busqueda" id="campoBusqueda" class="form-control"
               placeholder="Buscar empleado..." value="{{ request('busqueda') }}">
        <button type="submit" class="btn btn-buscar">Buscar</button>
      </form>
    </div>

    <div class="lado-derecho">
      {{-- Botón NUEVO controlado por permisos (front + JS) --}}
      <a href="#"
         class="btn btn-nuevo"
         id="btnMostrarModalEmpleado"
         data-bloqueado="{{ $accionesEmpleados['crear'] ? '0' : '1' }}">
        <i class="fas fa-plus"></i> Nuevo Empleado
      </a>

      <form method="GET" action="{{ route('empleados.index') }}" class="mostrar-registros">
        <label>Ordenar por</label>
        <select name="ordenar" onchange="this.form.submit()">
          <option value="nombre" {{ request('ordenar', 'nombre') == 'nombre' ? 'selected' : '' }}>Nombre (A-Z)</option>
          <option value="fecha" {{ request('ordenar') == 'fecha' ? 'selected' : '' }}>Fecha de contratación</option>
        </select>

        <label>Mostrar</label>
        <select name="cantidad" onchange="this.form.submit()">
          @foreach([5, 10, 15, 20] as $opcion)
            <option value="{{ $opcion }}" {{ request('cantidad', 5) == $opcion ? 'selected' : '' }}>{{ $opcion }}</option>
          @endforeach
        </select>
        <span>registros</span>
        <input type="hidden" name="busqueda" value="{{ request('busqueda') }}">
      </form>
    </div>
  </div>

  <div class="empleados-container">
    <table class="empleados-table">
      <thead>
      <tr>
        <th>Nombre</th>
        <th>DNI</th>
        <th>Email</th>
        <th>Teléfono</th>
        <th>Puesto</th>
        <th>Acciones</th>
      </tr>
      </thead>
      <tbody>
      @forelse ($empleados as $emp)
        <tr>
          <td>{{ $emp['nombre_completo'] }}</td>
          <td class="no-wrap">{{ $emp['dni'] }}</td>
          <td>{{ $emp['email_trabajo'] }}</td>
          <td class="no-wrap">{{ $emp['telefono'] }}</td>
          <td>{{ $emp['puesto'] }}</td>
          <td class="acciones-botones">
            <a href="#" class="btn btn-info btn-ver-detalles" data-empleado='@json($emp)'>Ver Detalles</a>

            {{-- Botón EDITAR solo si tiene permiso --}}
            @if($accionesEmpleados['actualizar'])
              <a href="#"
                 class="btn btn-warning btn-editar-empleado"
                 data-empleado='@json($emp)'>
                Editar
              </a>
            @endif

            {{-- Botón ELIMINAR solo si tiene permiso --}}
            @if($accionesEmpleados['eliminar'])
              <form method="POST"
                    action="{{ route('empleados.destroy', $emp['cod_empleado']) }}"
                    class="form-eliminar"
                    data-nombre="{{ $emp['nombre_completo'] }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-eliminar">Eliminar</button>
              </form>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center">No hay empleados registrados.</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  <div class="paginacion-wrapper">
    {{ $empleados->links('pagination::bootstrap-4') }}
  </div>
</div>

{{-- MODAL DETALLES --}}
<div class="modal-empleado" id="modalVerDetalles">
  <div class="modal-content perfil-modal">
    <span class="cerrar-modal" id="cerrarModalDetalles">&times;</span>
    <div class="perfil-empleado" id="contenidoDetallesEmpleado"></div>
  </div>
</div>

{{-- MODAL REGISTRO / EDICIÓN --}}
<div class="modal-empleado" id="modalRegistroEmpleado" style="display: none;">
  <div class="modal-content registro-empleado-modal">
    <span class="cerrar-modal" id="cerrarModalRegistro">&times;</span>

    <div class="pasos-linea">
      <div class="paso-item activo">
        <div class="paso-numero">1</div>
        <div class="paso-texto">General</div>
      </div>
      <div class="paso-linea-conector"></div>
      <div class="paso-item">
        <div class="paso-numero">2</div>
        <div class="paso-texto">Personal</div>
      </div>
      <div class="paso-linea-conector"></div>
      <div class="paso-item">
        <div class="paso-numero">3</div>
        <div class="paso-texto">Laboral</div>
      </div>
      <div class="paso-linea-conector"></div>
      <div class="paso-item">
        <div class="paso-numero">4</div>
        <div class="paso-texto">Resumen</div>
      </div>
    </div>

    <form id="formRegistroEmpleado" enctype="multipart/form-data">
      {{-- PASO 1 --}}
      <div class="paso paso-activo" id="paso1">
        <div class="resumen-empleado">
          <div class="titulo-paso">Información General</div>

          <label for="nombre_completo">Nombre completo</label>
          <div class="campo-grupo">
            <input type="text" id="nombre_completo" name="nombre_completo" placeholder="Nombre completo" required>
            <span class="error-badge" data-for="nombre_completo"></span>
          </div>

          <label for="dni">DNI</label>
          <div class="campo-grupo">
            <input type="text" id="dni" name="dni" placeholder="Ej: 0801-1999-12345"
                   required oninput="validarDNI()" onblur="validarDNI()">
            <span class="error-badge" data-for="dni"></span>
          </div>

          <label for="rtn">RTN</label>
          <div class="campo-grupo">
            <input type="text" id="rtn" name="rtn" placeholder="RTN del empleado"
                   oninput="validarRTN()" onblur="validarRTN()">
            <span class="error-badge" data-for="rtn"></span>
          </div>

          <label for="email_trabajo">Correo institucional</label>
          <div class="campo-grupo">
            <input type="email" id="email_trabajo" name="email_trabajo" placeholder="Correo institucional">
            <span class="error-badge" data-for="email_trabajo"></span>
          </div>

          <label for="telefono">Teléfono</label>
          <div class="campo-grupo">
            <input type="text" id="telefono" name="telefono" placeholder="+504 9999-9999">
            <span class="error-badge" data-for="telefono"></span>
          </div>

          <label for="direccion">Dirección</label>
          <div class="campo-grupo">
            <input type="text" id="direccion" name="direccion" placeholder="Dirección">
            <span class="error-badge" data-for="direccion"></span>
          </div>

          <label for="selectMunicipio">Municipio</label>
          <div class="campo-grupo">
            <select name="cod_municipio" id="selectMunicipio" required>
              <option value="">Seleccione municipio</option>
            </select>
            <span class="error-badge" data-for="selectMunicipio"></span>
          </div>
        </div>
      </div>

      {{-- PASO 2 --}}
      <div class="paso" id="paso2">
        <div class="resumen-empleado">
          <div class="titulo-paso">Información Personal</div>

          <label for="selectGenero">Género</label>
          <div class="campo-grupo">
            <select name="genero" id="selectGenero" required>
              <option value="">Seleccione género</option>
            </select>
            <span class="error-badge" data-for="selectGenero"></span>
          </div>

          <label for="selectEstadoCivil">Estado civil</label>
          <div class="campo-grupo">
            <select name="estado_civil" id="selectEstadoCivil" required>
              <option value="">Seleccione estado civil</option>
            </select>
            <span class="error-badge" data-for="selectEstadoCivil"></span>
          </div>

          <label for="fec_nacimiento">Fecha de nacimiento</label>
          <div class="campo-grupo">
            <input type="date" id="fec_nacimiento" name="fec_nacimiento" required>
            <span class="error-badge" data-for="fec_nacimiento"></span>
          </div>

          <label for="lugar_nacimiento">Lugar de nacimiento</label>
          <div class="campo-grupo">
            <input type="text" id="lugar_nacimiento" name="lugar_nacimiento" placeholder="Lugar de nacimiento" required>
            <span class="error-badge" data-for="lugar_nacimiento"></span>
          </div>

          <label for="nacionalidad">Nacionalidad</label>
          <div class="campo-grupo">
            <input type="text" id="nacionalidad" name="nacionalidad" placeholder="Nacionalidad" required>
            <span class="error-badge" data-for="nacionalidad"></span>
          </div>

          <label for="nombre_contacto_emergencia">Nombre del contacto de emergencia</label>
          <div class="campo-grupo">
            <input type="text" id="nombre_contacto_emergencia" name="nombre_contacto_emergencia"
                   placeholder="Contacto emergencia" required>
            <span class="error-badge" data-for="nombre_contacto_emergencia"></span>
          </div>

          <label for="telefono_emergencia">Teléfono del contacto de emergencia</label>
          <div class="campo-grupo">
            <input type="text" id="telefono_emergencia" name="telefono_emergencia"
                   placeholder="+504 9999-9999" required>
            <span class="error-badge" data-for="telefono_emergencia"></span>
          </div>
        </div>
      </div>

      {{-- PASO 3 --}}
      <div class="paso" id="paso3">
        <div class="resumen-empleado">
          <div class="titulo-paso">Información Laboral</div>

          <label for="selectModalidad">Modalidad de contratación</label>
          <div class="campo-grupo">
            <select name="cod_tipo_modalidad" id="selectModalidad" required>
              <option value="">Seleccione modalidad</option>
            </select>
            <span class="error-badge" data-for="selectModalidad"></span>
          </div>

          <label for="selectPuesto">Puesto</label>
          <div class="campo-grupo">
            <select name="cod_puesto" id="selectPuesto" required>
              <option value="">Seleccione puesto</option>
            </select>
            <span class="error-badge" data-for="selectPuesto"></span>
          </div>

          <label for="selectTipoEmpleado">Tipo de empleado</label>
          <div class="campo-grupo">
            <select name="cod_tipo_empleado" id="selectTipoEmpleado" required>
              <option value="">Seleccione tipo de empleado</option>
            </select>
            <span class="error-badge" data-for="selectTipoEmpleado"></span>
          </div>

          <label for="selectNivelEducativo">Nivel educativo</label>
          <div class="campo-grupo">
            <select name="cod_nivel_educativo" id="selectNivelEducativo" required>
              <option value="">Seleccione nivel educativo</option>
            </select>
            <span class="error-badge" data-for="selectNivelEducativo"></span>
          </div>

          <label for="selectOficina">Oficina</label>
          <div class="campo-grupo">
            <select name="cod_oficina" id="selectOficina" required>
              <option value="">Seleccione oficina</option>
            </select>
            <span class="error-badge" data-for="selectOficina"></span>
          </div>

          <label for="selectHorario">Horario laboral</label>
          <div class="campo-grupo">
            <select name="cod_horario" id="selectHorario" required>
              <option value="">Seleccione horario</option>
            </select>
            <span class="error-badge" data-for="selectHorario"></span>
          </div>

          <label for="fecha_contratacion">Fecha de contratación</label>
          <div class="campo-grupo">
            <input type="date" id="fecha_contratacion" name="fecha_contratacion" placeholder="Fecha contratación">
            <span class="error-badge" data-for="fecha_contratacion"></span>
          </div>

          <label for="fecha_inicio_contrato">Fecha de inicio de contrato</label>
          <div class="campo-grupo">
            <input type="date" id="fecha_inicio_contrato" name="fecha_inicio_contrato" required>
            <span class="error-badge" data-for="fecha_inicio_contrato"></span>
          </div>

          <label for="fecha_final_contrato">Fecha final de contrato</label>
          <div class="campo-grupo">
            <input type="date" id="fecha_final_contrato" name="fecha_final_contrato">
            <span class="error-badge" data-for="fecha_final_contrato"></span>
          </div>

          <label for="contrato_activo">Estado del contrato</label>
          <div class="campo-grupo">
            <select name="contrato_activo" id="contrato_activo" required>
              <option value="true" selected>Activo</option>
              <option value="false">No activo</option>
            </select>
            <span class="error-badge" data-for="contrato_activo"></span>
          </div>

          <label for="salario">Salario</label>
          <div class="campo-grupo">
            <input type="text" id="salario" placeholder="L. 0.00">
            <input type="hidden" name="salario" id="salario_real">
            <span class="error-badge" data-for="salario"></span>
          </div>
        </div>
      </div>

      {{-- PASO 4 --}}
      <div class="paso" id="paso4">
        <h4>Resumen</h4>
        <div id="resumenEmpleado" class="resumen-empleado">
          <h4 id="resumen-nombre"></h4>
          <p id="resumen-dni" class="resumen-dni"></p>
          <p id="resumen-rtn" class="resumen-dni"></p>

          <div class="resumen-bloque">
            <h5>Información General</h5>
            <p><strong>Email:</strong> <span id="resumen-email"></span></p>
            <p><strong>Teléfono:</strong> <span id="resumen-telefono"></span></p>
            <p><strong>Dirección:</strong> <span id="resumen-direccion"></span></p>
            <p><strong>Municipio:</strong> <span id="resumen-municipio"></span></p>
            <p><strong>Departamento:</strong> <span id="resumen-departamento">Pendiente</span></p>
          </div>

          <div class="resumen-bloque">
            <h5>Información Personal</h5>
            <p><strong>Género:</strong> <span id="resumen-genero"></span></p>
            <p><strong>Estado Civil:</strong> <span id="resumen-estado-civil"></span></p>
            <p><strong>Fecha de Nacimiento:</strong> <span id="resumen-fecha-nacimiento"></span></p>
            <p><strong>Lugar de Nacimiento:</strong> <span id="resumen-lugar-nacimiento"></span></p>
            <p><strong>Nacionalidad:</strong> <span id="resumen-nacionalidad"></span></p>
            <p><strong>Contacto de Emergencia:</strong> <span id="resumen-contacto-emergencia"></span></p>
            <p><strong>Tel. Emergencia:</strong> <span id="resumen-telefono-emergencia"></span></p>
          </div>

          <div class="resumen-bloque">
            <h5>Información Laboral</h5>
            <p><strong>Puesto:</strong> <span id="resumen-puesto"></span></p>
            <p><strong>Modalidad:</strong> <span id="resumen-modalidad"></span></p>
            <p><strong>Tipo de Empleado:</strong> <span id="resumen-tipo-empleado"></span></p>
            <p><strong>Horario:</strong> <span id="resumen-horario"></span></p>
            <p><strong>Nivel Educativo:</strong> <span id="resumen-nivel-educativo"></span></p>
            <p><strong>Oficina:</strong> <span id="resumen-oficina"></span></p>
            <p><strong>Fecha Contratación:</strong> <span id="resumen-fecha-contratacion"></span></p>
            <p><strong>Fecha Inicio Contrato:</strong> <span id="resumen-fecha-inicio"></span></p>
            <p><strong>Fecha Final Contrato:</strong> <span id="resumen-fecha-final"></span></p>
            <p><strong>Contrato Activo:</strong> <span id="resumen-contrato-activo">Sí</span></p>
            <p><strong>Salario:</strong> L. <span id="resumen-salario"></span></p>
          </div>
        </div>
      </div>

      <div class="wizard-buttons">
        <button type="button" id="btnAnterior" onclick="anteriorPaso()">Anterior</button>
        <button type="button" id="btnSiguiente" onclick="siguientePaso()">Siguiente</button>
        <button type="submit" id="btnGuardar" style="display: none;">Guardar</button>
        <button type="button" onclick="cerrarModalRegistro()">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // =======================
  // PERMISOS (desde backend)
  // =======================
  const P_CAN_CREATE_EMPLEADO = {{ $accionesEmpleados['crear'] ? 'true' : 'false' }};
  const P_CAN_UPDATE_EMPLEADO = {{ $accionesEmpleados['actualizar'] ? 'true' : 'false' }};
  const P_CAN_DELETE_EMPLEADO = {{ $accionesEmpleados['eliminar'] ? 'true' : 'false' }};

  // =======================
  // VARIABLES
  // =======================
  let modoEdicion = false;
  let empleadoEditandoId = null;
  let pasoActual = 1;
  let dniOriginalEdicion = null;
  let rtnOriginalEdicion = null;

  const camposRequeridosPorPaso = {
    1: ['nombre_completo', 'dni','rtn', 'email_trabajo', 'telefono', 'direccion', 'selectMunicipio'],
    2: ['selectGenero', 'selectEstadoCivil', 'fec_nacimiento', 'lugar_nacimiento',
        'nacionalidad', 'nombre_contacto_emergencia', 'telefono_emergencia'],
    3: ['selectModalidad', 'selectPuesto', 'selectTipoEmpleado', 'selectNivelEducativo',
        'selectOficina', 'selectHorario', 'fecha_contratacion',
        'fecha_inicio_contrato', 'fecha_final_contrato', 'contrato_activo', 'salario']
  };

  // =======================
  // UTILIDADES ERRORES
  // =======================
  function getErrorBadge(id) {
    return document.querySelector(`.error-badge[data-for="${id}"]`);
  }

  function setMensajeError(id, mensaje = '') {
    const badge = getErrorBadge(id);
    if (!badge) return;
    badge.textContent = mensaje;
    badge.style.display = mensaje ? 'inline-block' : 'none';
  }

  function marcarError(id, tieneError) {
    const el = document.getElementById(id);
    if (!el) return;
    if (tieneError) {
      el.classList.add('error');
    } else {
      el.classList.remove('error');
    }
  }

  function limpiarErrores() {
    Object.values(camposRequeridosPorPaso).flat().forEach(id => {
      marcarError(id, false);
      setMensajeError(id, '');
    });
  }

  // =======================
  // VALIDACIONES
  // =======================
  function validarBusqueda() {
    const valor = document.getElementById('campoBusqueda').value.trim();
    if (valor.includes(' ')) {
      Swal.fire({
        icon: 'warning',
        title: 'Búsqueda inválida',
        text: 'Solo se permite una palabra sin espacios.',
        timer: 2000,
        showConfirmButton: false
      });
      return false;
    }
    return true;
  }

  function validarCampo(id) {
    const el = document.getElementById(id);
    if (!el) return true;
    let valor = (el.value || '').trim();
    let valido = true;
    let msg = '';

    switch (id) {
      case 'nombre_completo': {
        const palabras = valor.split(/\s+/).filter(Boolean);
        if (palabras.length === 0) {
          valido = false;
          msg = 'Campo obligatorio.';
        } else if (palabras.length > 4) {
          valido = false;
          msg = 'Máximo 4 nombres.';
        }
        break;
      }

      case 'nombre_contacto_emergencia': {
        const palabras = valor.split(/\s+/).filter(Boolean);
        if (palabras.length === 0) {
          valido = false;
          msg = 'Campo obligatorio.';
        } else if (palabras.length > 4) {
          valido = false;
          msg = 'Máximo 4 nombres.';
        }
        break;
      }

      case 'dni': {
        const digits = valor.replace(/\D/g, '');
        const duplicado = el.dataset.duplicado === '1';
        if (digits.length !== 13) {
          valido = false;
          msg = 'Debe tener 13 dígitos.';
        } else if (duplicado) {
          valido = false;
          msg = 'DNI ya registrado.';
        }
        break;
      }

      case 'rtn': {
        const digits = valor.replace(/\D/g, '');
        const duplicado = el.dataset.duplicado === '1';

        if (digits.length === 0) {
          valido = false;
          msg = 'Campo obligatorio.';
        } else if (digits.length !== 14) {
          valido = false;
          msg = 'Debe tener 14 dígitos.';
        } else if (duplicado) {
          valido = false;
          msg = 'RTN ya registrado.';
        }
        break;
      }

      case 'email_trabajo':
        if (valor === '') {
          valido = false;
          msg = 'Campo obligatorio.';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) {
          valido = false;
          msg = 'Correo inválido.';
        }
        break;

      case 'telefono':
      case 'telefono_emergencia': {
        const digitsT = valor.replace(/\D/g, '');
        if (digitsT.length !== 11) {
          valido = false;
          msg = 'Teléfono inválido.';
        }
        break;
      }

      case 'fec_nacimiento': {
        if (!valor) {
          valido = false;
          msg = '';
          break;
        }
        const fecha = new Date(valor);
        const hoy = new Date();
        const year = fecha.getFullYear();
        if (isNaN(fecha.getTime()) || year < 1900 || fecha > hoy) {
          valido = false;
          msg = 'Fecha de nacimiento no válida.';
        }
        break;
      }

      case 'fecha_inicio_contrato':
      case 'fecha_contratacion':
      case 'fecha_final_contrato': {
        valido = !!valor;
        msg = '';
        break;
      }

      case 'salario': {
        const real = document.getElementById('salario_real').value;
        valido = real !== '' && Number(real) > 0;
        if (!valido) msg = 'Ingrese salario.';
        break;
      }

      default:
        if (el.tagName === 'SELECT') {
          valido = valor !== '';
          msg = '';
        } else {
          valido = valor !== '';
          if (!valido) msg = 'Campo obligatorio.';
        }
    }

    marcarError(id, !valido);
    setMensajeError(id, msg);
    return valido;
  }

  function validarPaso(paso) {
    const campos = camposRequeridosPorPaso[paso] || [];
    let ok = true;
    campos.forEach(id => {
      if (!validarCampo(id)) ok = false;
    });
    return ok;
  }

  function validarTodosLosPasos() {
    if (!validarPaso(1) || !validarPaso(2) || !validarPaso(3)) return false;
    if (document.querySelector('.campo-grupo input.error, .campo-grupo select.error')) {
      return false;
    }
    return true;
  }

  function actualizarBotones() {
    const btnSig = document.getElementById('btnSiguiente');
    const btnGuardar = document.getElementById('btnGuardar');

    if (pasoActual < 4) {
      const valido = validarPaso(pasoActual);
      btnSig.disabled = !valido;
      if (btnGuardar) btnGuardar.disabled = true;
    } else {
      if (btnSig) btnSig.disabled = true;
      if (btnGuardar) btnGuardar.disabled = !validarTodosLosPasos();
    }
  }

  // =======================
  // VALIDACIÓN DNI (API)
  // =======================
  async function validarDNI() {
    const dniInput = document.getElementById('dni');
    const dni = dniInput.value.trim();

    setMensajeError('dni', '');
    dniInput.classList.remove('error');
    delete dniInput.dataset.duplicado;

    if (dni === '') {
      actualizarBotones();
      return;
    }

    const digits = dni.replace(/\D/g, '');
    if (digits.length !== 13) {
      dniInput.classList.add('error');
      setMensajeError('dni', 'Debe tener 13 dígitos.');
      actualizarBotones();
      return;
    }

    if (modoEdicion && dniOriginalEdicion && dni === dniOriginalEdicion) {
      actualizarBotones();
      return;
    }

    try {
      const response = await fetch(
        `https://rrhh-didadpol-1.onrender.com/api/personas/dni/${encodeURIComponent(dni)}`
      );
      const data = await response.json();

      if (response.ok && data.existe) {
        dniInput.classList.add('error');
        dniInput.dataset.duplicado = '1';
        setMensajeError('dni', 'DNI ya registrado.');
      } else {
        dniInput.classList.remove('error');
        delete dniInput.dataset.duplicado;
        setMensajeError('dni', '');
      }
    } catch (error) {
      console.error('Error al verificar el DNI:', error);
      dniInput.classList.add('error');
      setMensajeError('dni', 'Error al verificar el DNI.');
    }

    actualizarBotones();
  }

  // =======================
  // VALIDACIÓN RTN (API)
  // =======================
  async function validarRTN() {
    const rtnInput = document.getElementById('rtn');
    const rtn = rtnInput.value.trim();

    setMensajeError('rtn', '');
    rtnInput.classList.remove('error');
    delete rtnInput.dataset.duplicado;

    if (rtn === '') {
      rtnInput.classList.add('error');
      setMensajeError('rtn', 'Campo obligatorio.');
      actualizarBotones();
      return;
    }

    const digits = rtn.replace(/\D/g, '');
    if (digits.length !== 14) {
      rtnInput.classList.add('error');
      setMensajeError('rtn', 'Debe tener 14 dígitos.');
      actualizarBotones();
      return;
    }

    if (modoEdicion && rtnOriginalEdicion && digits === rtnOriginalEdicion.replace(/\D/g, '')) {
      actualizarBotones();
      return;
    }

    try {
      const response = await fetch(
        `https://rrhh-didadpol-1.onrender.com/api/personas/rtn/${encodeURIComponent(digits)}`
      );
      const data = await response.json();

      if (response.ok && data.existe) {
        rtnInput.classList.add('error');
        rtnInput.dataset.duplicado = '1';
        setMensajeError('rtn', 'RTN ya registrado.');
      } else {
        rtnInput.classList.remove('error');
        delete rtnInput.dataset.duplicado;
        setMensajeError('rtn', '');
      }

    } catch (error) {
      console.error('Error al verificar el RTN:', error);
      rtnInput.classList.add('error');
      setMensajeError('rtn', 'Error al verificar el RTN.');
    }

    actualizarBotones();
  }

  // =======================
  // LISTENERS CAMPOS
  // =======================
  document.getElementById('nombre_completo').addEventListener('input', (e) => {
    let val = e.target.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ\s]/g, '');
    e.target.value = val;
    validarCampo('nombre_completo');
    actualizarBotones();
  });

  document.getElementById('direccion').addEventListener('input', (e) => {
    e.target.value = e.target.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ0-9 #.,-]/g, '');
    validarCampo('direccion');
    actualizarBotones();
  });

  document.getElementById('dni').addEventListener('input', async (e) => {
    let val = e.target.value.replace(/\D/g, '');
    if (val.length > 13) val = val.slice(0, 13);

    let formatted = val;
    if (val.length > 4)  formatted = val.slice(0, 4) + '-' + val.slice(4);
    if (val.length > 8)  formatted = formatted.slice(0, 9) + '-' + val.slice(8);

    e.target.value = formatted;
    await validarDNI();
  });

  document.getElementById('rtn').addEventListener('input', async (e) => {
    let val = e.target.value.replace(/\D/g, '');
    if (val.length > 14) val = val.slice(0, 14);
    e.target.value = val;

    validarCampo('rtn');
    await validarRTN();
    actualizarBotones();
  });

  document.getElementById('telefono').addEventListener('input', (e) => {
    let val = e.target.value.replace(/\D/g, '');
    if (!val.startsWith('504')) val = '504' + val;
    if (val.length > 11) val = val.slice(0, 11);
    const formatted = '+504 ' + val.slice(3, 7) + '-' + val.slice(7, 11);
    e.target.value = formatted.trim();
    validarCampo('telefono');
    actualizarBotones();
  });

  document.getElementById('telefono_emergencia').addEventListener('input', (e) => {
    let val = e.target.value.replace(/\D/g, '');
    if (!val.startsWith('504')) val = '504' + val;
    if (val.length > 11) val = val.slice(0, 11);
    const formatted = '+504 ' + val.slice(3, 7) + '-' + val.slice(7, 11);
    e.target.value = formatted.trim();
    validarCampo('telefono_emergencia');
    actualizarBotones();
  });

  document.getElementById('lugar_nacimiento').addEventListener('input', (e) => {
    e.target.value = e.target.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ ,.]/g, '');
    validarCampo('lugar_nacimiento');
    actualizarBotones();
  });

  document.getElementById('nacionalidad').addEventListener('input', (e) => {
    e.target.value = e.target.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ ]/g, '');
    validarCampo('nacionalidad');
    actualizarBotones();
  });

  document.getElementById('nombre_contacto_emergencia').addEventListener('input', (e) => {
    e.target.value = e.target.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ ]/g, '');
    validarCampo('nombre_contacto_emergencia');
    actualizarBotones();
  });

  document.getElementById('email_trabajo').addEventListener('input', () => {
    validarCampo('email_trabajo');
    actualizarBotones();
  });

  document.getElementById('fec_nacimiento').addEventListener('change', () => {
    validarCampo('fec_nacimiento');
    actualizarBotones();
  });

  document.getElementById('fecha_inicio_contrato').addEventListener('change', () => {
    validarCampo('fecha_inicio_contrato');
    actualizarBotones();
  });

  document.getElementById('fecha_contratacion').addEventListener('change', () => {
    validarCampo('fecha_contratacion');
    actualizarBotones();
  });

  document.getElementById('fecha_final_contrato').addEventListener('change', () => {
    validarCampo('fecha_final_contrato');
    actualizarBotones();
  });

  [
    'selectMunicipio', 'selectGenero', 'selectEstadoCivil', 'selectModalidad',
    'selectPuesto', 'selectTipoEmpleado', 'selectNivelEducativo',
    'selectOficina', 'selectHorario', 'contrato_activo'
  ].forEach(id => {
    const sel = document.getElementById(id);
    if (!sel) return;
    sel.addEventListener('change', () => {
      validarCampo(id);
      actualizarBotones();
    });
  });

  document.getElementById('salario').addEventListener('input', (e) => {
    let valor = e.target.value.replace(/[^\d]/g, '');
    const salarioRealInput = document.getElementById('salario_real');

    if (valor === '') {
      e.target.value = '';
      salarioRealInput.value = '';
      validarCampo('salario');
      actualizarBotones();
      return;
    }

    let formateado = Number(valor / 100).toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

    e.target.value = `L. ${formateado}`;
    salarioRealInput.value = (valor / 100).toFixed(2);

    validarCampo('salario');
    actualizarBotones();
  });

  // =======================
  // MANEJO DE PASOS
  // =======================
  function mostrarPaso(n) {
    pasoActual = n;

    document.querySelectorAll('.paso').forEach((p, i) => {
      p.style.display = (i + 1 === n) ? 'block' : 'none';
    });

    document.querySelectorAll('.paso-item').forEach((item, i) => {
      item.classList.remove('activo');
      if (i < n) item.classList.add('activo');
    });

    document.getElementById('btnAnterior').style.display   = (n === 1) ? 'none' : 'inline-block';
    document.getElementById('btnSiguiente').style.display  = (n === 4) ? 'none' : 'inline-block';
    document.getElementById('btnGuardar').style.display    = (n === 4) ? 'inline-block' : 'none';

    if (n === 4) {
      generarResumen();
    } else {
      validarPaso(n);
    }

    const modalContent = document.querySelector('#modalRegistroEmpleado .modal-content');
    if (modalContent) modalContent.scrollTop = 0;

    actualizarBotones();
  }

  function siguientePaso() {
    if (pasoActual < 4 && validarPaso(pasoActual)) {
      mostrarPaso(pasoActual + 1);
    } else {
      actualizarBotones();
    }
  }

  function anteriorPaso() {
    if (pasoActual > 1) {
      mostrarPaso(pasoActual - 1);
    }
  }

  function cerrarModalRegistro() {
    document.getElementById('modalRegistroEmpleado').style.display = 'none';
    modoEdicion = false;
    empleadoEditandoId = null;
    dniOriginalEdicion = null;
    rtnOriginalEdicion = null;
    document.getElementById('formRegistroEmpleado').reset();
    limpiarErrores();
  }

  document.getElementById('btnMostrarModalEmpleado')?.addEventListener('click', () => {
    // 🔐 Verificación de permiso CREAR
    if (!P_CAN_CREATE_EMPLEADO) {
      Swal.fire({
        icon: 'error',
        title: 'Acción no permitida',
        text: 'No tienes permiso para registrar nuevos empleados.'
      });
      return;
    }

    document.getElementById('modalRegistroEmpleado').style.display = 'flex';
    pasoActual = 1;
    modoEdicion = false;
    dniOriginalEdicion = null;
    rtnOriginalEdicion = null;
    limpiarErrores();
    mostrarPaso(1);
    cargarDatosSelects().then(() => {
      actualizarBotones();
    });
  });

  document.getElementById('cerrarModalRegistro').addEventListener('click', cerrarModalRegistro);
  window.addEventListener('click', (e) => {
    if (e.target === document.getElementById('modalRegistroEmpleado')) cerrarModalRegistro();
  });
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') cerrarModalRegistro();
  });

  // =======================
  // CARGA DE SELECTS
  // =======================
  function cargarSelect(url, selectId, nombreCampo = 'nombre', valorCampo = 'id') {
    const select = document.getElementById(selectId);
    if (!select) return Promise.resolve();

    select.innerHTML = `<option value="">Cargando...</option>`;

    return fetch(url)
      .then(res => res.json())
      .then(data => {
        select.innerHTML = `<option value="">Seleccione una opción</option>`;
        data.forEach(item => {
          const opt = document.createElement('option');
          opt.value = item[valorCampo];
          opt.textContent = item[nombreCampo];
          select.appendChild(opt);
        });
      })
      .catch(err => {
        console.error(`Error al cargar ${selectId}:`, err);
        select.innerHTML = `<option value="">(Error al cargar)</option>`;
      });
  }

  function cargarDatosSelects() {
    return Promise.all([
      cargarSelect('https://rrhh-didadpol-1.onrender.com/api/municipios',        'selectMunicipio',    'nombre', 'cod_municipio'),
      cargarSelect('https://rrhh-didadpol-1.onrender.com/api/generos',           'selectGenero',       'nombre', 'nombre'),
      cargarSelect('https://rrhh-didadpol-1.onrender.com/api/estados-civiles',   'selectEstadoCivil',  'nombre', 'nombre'),
      cargarSelect('https://rrhh-didadpol-1.onrender.com/api/modalidades',       'selectModalidad',    'nombre', 'cod_tipo_modalidad'),
      cargarSelect('https://rrhh-didadpol-1.onrender.com/api/puestos',           'selectPuesto',       'nombre', 'cod_puesto'),
      cargarSelect('https://rrhh-didadpol-1.onrender.com/api/niveles-educativos','selectNivelEducativo','nombre','cod_nivel_educativo'),
      cargarSelect('https://rrhh-didadpol-1.onrender.com/api/oficinas',          'selectOficina',      'nombre', 'cod_oficina'),
      cargarSelect('https://rrhh-didadpol-1.onrender.com/api/horarios',          'selectHorario',      'nombre', 'cod_horario'),
      cargarSelect('https://rrhh-didadpol-1.onrender.com/api/tipos-empleados',   'selectTipoEmpleado', 'nom_tipo','cod_tipo_empleado')
    ]);
  }

  function getTextoSelect(id) {
    const select = document.getElementById(id);
    return select.options[select.selectedIndex]?.text || '';
  }

  // =======================
  // RESUMEN
  // =======================
  function generarResumen() {
    const form = document.getElementById('formRegistroEmpleado');
    const data = new FormData(form);

    document.getElementById('resumen-nombre').textContent = data.get('nombre_completo') || '';
    document.getElementById('resumen-dni').textContent = data.get('dni') || '';
    document.getElementById('resumen-rtn').textContent = data.get('rtn')
      ? `RTN: ${data.get('rtn')}` : 'RTN: —';

    document.getElementById('resumen-email').textContent = data.get('email_trabajo') || '';
    document.getElementById('resumen-telefono').textContent = data.get('telefono') || '';
    document.getElementById('resumen-direccion').textContent = data.get('direccion') || '';
    document.getElementById('resumen-municipio').textContent = getTextoSelect('selectMunicipio');

    document.getElementById('resumen-genero').textContent = getTextoSelect('selectGenero');
    document.getElementById('resumen-estado-civil').textContent = getTextoSelect('selectEstadoCivil');
    document.getElementById('resumen-fecha-nacimiento').textContent = data.get('fec_nacimiento') || '';
    document.getElementById('resumen-lugar-nacimiento').textContent = data.get('lugar_nacimiento') || '';
    document.getElementById('resumen-nacionalidad').textContent = data.get('nacionalidad') || '';
    document.getElementById('resumen-contacto-emergencia').textContent =
      data.get('nombre_contacto_emergencia') || '';
    document.getElementById('resumen-telefono-emergencia').textContent =
      data.get('telefono_emergencia') || '';

    document.getElementById('resumen-puesto').textContent = getTextoSelect('selectPuesto');
    document.getElementById('resumen-tipo-empleado').textContent = getTextoSelect('selectTipoEmpleado');
    document.getElementById('resumen-modalidad').textContent = getTextoSelect('selectModalidad');
    document.getElementById('resumen-horario').textContent = getTextoSelect('selectHorario');
    document.getElementById('resumen-nivel-educativo').textContent = getTextoSelect('selectNivelEducativo');
    document.getElementById('resumen-oficina').textContent = getTextoSelect('selectOficina');
    document.getElementById('resumen-fecha-contratacion').textContent = data.get('fecha_contratacion') || '';
    document.getElementById('resumen-fecha-inicio').textContent = data.get('fecha_inicio_contrato') || '';
    document.getElementById('resumen-fecha-final').textContent = data.get('fecha_final_contrato') || '';

    let salarioRaw = document.getElementById('salario_real').value || '';
    document.getElementById('resumen-salario').textContent = salarioRaw
      ? Number(salarioRaw).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
      : '';
  }

  function setSalario(valor) {
    const visible = document.getElementById('salario');
    const hidden  = document.getElementById('salario_real');

    if (valor == null || valor === '') {
      visible.value = '';
      hidden.value  = '';
      return;
    }
    const num = Number(String(valor).replace(/[^\d.]/g, '')) || Number(valor);
    visible.value = 'L. ' + num.toLocaleString('es-HN', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
    hidden.value  = num.toFixed(2);
  }

  // =======================
  // ELIMINAR / EDITAR / DETALLES
  // =======================
  document.querySelectorAll('.form-eliminar').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();

      // 🔐 Permiso ELIMINAR
      if (!P_CAN_DELETE_EMPLEADO) {
        Swal.fire({
          icon: 'error',
          title: 'Acción no permitida',
          text: 'No tienes permiso para eliminar empleados.'
        });
        return;
      }

      const nombre = this.dataset.nombre;
      Swal.fire({
        title: '¿Estás seguro?',
        text: `El empleado "${nombre}" se eliminará permanently.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) this.submit();
      });
    });
  });

  document.querySelectorAll('.btn-editar-empleado').forEach(btn => {
    btn.addEventListener('click', async () => {
      // 🔐 Permiso EDITAR
      if (!P_CAN_UPDATE_EMPLEADO) {
        Swal.fire({
          icon: 'error',
          title: 'Acción no permitida',
          text: 'No tienes permiso para editar empleados.'
        });
        return;
      }

      const datos = JSON.parse(btn.getAttribute('data-empleado'));
      modoEdicion = true;
      empleadoEditandoId = datos.cod_empleado;
      dniOriginalEdicion = datos.dni || null;
      rtnOriginalEdicion = datos.rtn || null;

      pasoActual = 1;
      limpiarErrores();

      await cargarDatosSelects();

      const form = document.getElementById('formRegistroEmpleado');
      form.nombre_completo.value = datos.nombre_completo || '';
      form.dni.value             = datos.dni || '';
      form.rtn.value             = datos.rtn || '';
      form.email_trabajo.value   = datos.email_trabajo || '';
      form.telefono.value        = datos.telefono || '';
      form.direccion.value       = datos.direccion || '';

      form.nombre_contacto_emergencia.value = datos.nombre_contacto_emergencia || '';
      form.telefono_emergencia.value        = datos.telefono_emergencia || '';
      form.fec_nacimiento.value             = datos.fec_nacimiento?.substring(0,10) || '';
      form.lugar_nacimiento.value           = datos.lugar_nacimiento || '';
      form.nacionalidad.value               = datos.nacionalidad || '';

      document.getElementById('selectMunicipio').value      = datos.cod_municipio || '';
      document.getElementById('selectGenero').value         = datos.genero || '';
      document.getElementById('selectEstadoCivil').value    = datos.estado_civil || '';
      document.getElementById('selectModalidad').value      = datos.cod_tipo_modalidad || '';
      document.getElementById('selectPuesto').value         = datos.cod_puesto || '';
      document.getElementById('selectTipoEmpleado').value   = datos.cod_tipo_empleado || '';
      document.getElementById('selectNivelEducativo').value = datos.cod_nivel_educativo || '';
      document.getElementById('selectOficina').value        = datos.cod_oficina || '';
      document.getElementById('selectHorario').value        = datos.cod_horario || '';
      document.getElementById('contrato_activo').value      = datos.contrato_activo ? 'true' : 'false';

      form.fecha_contratacion.value    = datos.fecha_contratacion?.substring(0,10) || '';
      form.fecha_inicio_contrato.value = datos.fecha_inicio_contrato?.substring(0,10) || '';
      form.fecha_final_contrato.value  = datos.fecha_final_contrato?.substring(0,10) || '';

      setSalario(datos.salario);

      mostrarPaso(1);
      document.getElementById('modalRegistroEmpleado').style.display = 'flex';

      Object.values(camposRequeridosPorPaso).flat().forEach(id => validarCampo(id));
      actualizarBotones();
    });
  });

  document.querySelectorAll('.btn-ver-detalles').forEach(btn => {
    btn.addEventListener('click', () => {
      const datos = JSON.parse(btn.getAttribute('data-empleado'));
      const contenido = document.getElementById('contenidoDetallesEmpleado');

      const salarioFormateado = datos.salario
        ? 'L. ' + Number(datos.salario).toLocaleString('es-HN', { minimumFractionDigits: 2 })
        : '-';

      contenido.innerHTML = `
        <div class="perfil-header">
          <h3 class="nombre-empleado-resumen">${datos.nombre_completo || '-'}</h3>
          <div class="documentos-linea">
            <p class="dni-empleado-resumen">${datos.dni || '-'}</p>
            <p class="dni-empleado-resumen">RTN: ${datos.rtn || '-'}</p>
          </div>
        </div>

        <div class="perfil-detalles-vertical">
          <div class="info-bloque">
            <h4 class="subtitulo">Información General</h4>
            <div class="campo-linea"><strong>Email:</strong> <span>${datos.email_trabajo || '-'}</span></div>
            <div class="campo-linea"><strong>Teléfono:</strong> <span>${datos.telefono || '-'}</span></div>
            <div class="campo-linea"><strong>Dirección:</strong> <span>${datos.direccion || '-'}</span></div>
            <div class="campo-linea"><strong>Municipio:</strong> <span>${datos.nom_municipio || '-'}</span></div>
            <div class="campo-linea"><strong>Departamento:</strong> <span>${datos.departamento || '-'}</span></div>
          </div>

          <div class="info-bloque">
            <h4 class="subtitulo">Información Personal</h4>
            <div class="campo-linea"><strong>Género:</strong> <span>${datos.genero || '-'}</span></div>
            <div class="campo-linea"><strong>Estado Civil:</strong> <span>${datos.estado_civil || '-'}</span></div>
            <div class="campo-linea"><strong>Fecha de Nacimiento:</strong> <span>${datos.fec_nacimiento?.substring(0,10) || '-'}</span></div>
            <div class="campo-linea"><strong>Lugar de Nacimiento:</strong> <span>${datos.lugar_nacimiento || '-'}</span></div>
            <div class="campo-linea"><strong>Nacionalidad:</strong> <span>${datos.nacionalidad || '-'}</span></div>
            <div class="campo-linea"><strong>Contacto de Emergencia:</strong> <span>${datos.nombre_contacto_emergencia || '-'}</span></div>
            <div class="campo-linea"><strong>Tel. Emergencia:</strong> <span>${datos.telefono_emergencia || '-'}</span></div>
          </div>

          <div class="info-bloque">
            <h4 class="subtitulo">Información Laboral</h4>
            <div class="campo-linea"><strong>Puesto:</strong> <span>${datos.puesto || '-'}</span></div>
            <div class="campo-linea"><strong>Modalidad:</strong> <span>${datos.modalidad || '-'}</span></div>
            <div class="campo-linea"><strong>Horario:</strong> <span>${datos.nombre_horario || '-'}</span></div>
            <div class="campo-linea"><strong>Nivel Educativo:</strong> <span>${datos.nivel_educativo || '-'}</span></div>
            <div class="campo-linea"><strong>Oficina:</strong> <span>${datos.nombre_oficina || '-'}</span></div>
            <div class="campo-linea"><strong>Fecha Contratación:</strong> <span>${datos.fecha_contratacion?.substring(0,10) || '-'}</span></div>
            <div class="campo-linea"><strong>Fecha Inicio Contrato:</strong> <span>${datos.fecha_inicio_contrato?.substring(0,10) || '-'}</span></div>
            <div class="campo-linea"><strong>Fecha Final Contrato:</strong> <span>${datos.fecha_final_contrato?.substring(0,10) || '-'}</span></div>
            <div class="campo-linea"><strong>Contrato Activo:</strong> <span>${datos.contrato_activo ? 'Sí' : 'No'}</span></div>
            <div class="campo-linea"><strong>Salario:</strong> <span>${salarioFormateado}</span></div>
          </div>
        </div>
      `;

      document.getElementById('modalVerDetalles').style.display = 'flex';
    });
  });

  document.getElementById('cerrarModalDetalles').addEventListener('click', () => {
    document.getElementById('modalVerDetalles').style.display = 'none';
  });
  window.addEventListener('click', (e) => {
    if (e.target === document.getElementById('modalVerDetalles')) {
      document.getElementById('modalVerDetalles').style.display = 'none';
    }
  });
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      document.getElementById('modalVerDetalles').style.display = 'none';
    }
  });

  // =======================
  // ENVÍO FORMULARIO
  // =======================
  document.getElementById('formRegistroEmpleado').addEventListener('submit', async function(e) {
    e.preventDefault();

    await validarDNI();
    await validarRTN();

    if (!validarTodosLosPasos()) {
      pasoActual = 1;
      mostrarPaso(1);
      return;
    }

    const formData = new FormData(this);
    const url = modoEdicion
      ? `https://rrhh-didadpol-1.onrender.com/api/empleados/${empleadoEditandoId}`
      : 'https://rrhh-didadpol-1.onrender.com/api/empleados';
    const metodo = modoEdicion ? 'PUT' : 'POST';

    fetch(url, {
      method: metodo,
      body: formData
    })
      .then(response => {
        if (!response.ok) {
          return response.json().then(data => {
            throw new Error(data.message || 'Error en el registro/edición');
          });
        }
        return response.json();
      })
      .then(data => {
        document.getElementById('modalRegistroEmpleado').style.display = 'none';
        Swal.fire({
          icon: 'success',
          title: modoEdicion ? 'Empleado actualizado' : 'Empleado registrado',
          text: data.message || 'Operación exitosa',
          confirmButtonColor: '#3085d6'
        }).then(() => {
          location.reload();
        });
      })
      .catch(error => {
        console.error('Error al guardar empleado:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'No se pudo guardar el empleado'
        });
      });
  });
</script>

@endsection
