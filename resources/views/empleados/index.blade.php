@extends('layouts.dashboard')
@section('title', 'Gestión de Empleados')

@section('content')

<!-- NOTIFICACIONES Y ALERTAS -->

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


<!-- CONTENIDO PRINCIPAL -->

<div class="empleados-wrapper">
  <div class="titulo-con-linea">
    <h2>Gestión de Empleados</h2>
  </div>

  <!-- BARRA SUPERIOR CON BÚSQUEDA Y ACCIONES -->
  <div class="acciones-superiores">
    <div class="lado-izquierdo">
      <form method="GET" action="{{ route('empleados.index') }}" class="form-busqueda" onsubmit="return validarBusqueda()">
        <input type="text" name="busqueda" id="campoBusqueda" class="form-control" placeholder="Buscar empleado..." value="{{ request('busqueda') }}">
        <button type="submit" class="btn btn-buscar">Buscar</button>
      </form>
    </div>

    <div class="lado-derecho">
      <a href="#" class="btn btn-nuevo" id="btnMostrarModalEmpleado">
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

  <!-- TABLA DE EMPLEADOS -->
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
            <a href="#" class="btn btn-warning btn-editar-empleado" data-empleado='@json($emp)'>Editar</a>
            <form method="POST" action="{{ route('empleados.destroy', $emp['cod_empleado']) }}" class="form-eliminar" data-nombre="{{ $emp['nombre_completo'] }}">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger btn-eliminar">Eliminar</button>
            </form>
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

  <!-- PAGINACIÓN -->
  <div class="paginacion-wrapper">
    {{ $empleados->links('pagination::bootstrap-4') }}
  </div>
</div>

<!-- MODALES -->

<!-- MODAL VER DETALLES -->
<div class="modal-empleado" id="modalVerDetalles">
  <div class="modal-content perfil-modal">
    <span class="cerrar-modal" id="cerrarModalDetalles">&times;</span>
    <div class="perfil-empleado" id="contenidoDetallesEmpleado"></div>
  </div>
</div>

<!-- MODAL REGISTRO/EDICIÓN -->
<div class="modal-empleado" id="modalRegistroEmpleado" style="display: none;">
  <div class="modal-content registro-empleado-modal">
    <span class="cerrar-modal" id="cerrarModalRegistro">&times;</span>

    <!-- PASOS DEL FORMULARIO -->
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
      <!-- PASO 1: INFORMACIÓN GENERAL -->
      <div class="paso paso-activo" id="paso1">
        <div class="resumen-empleado">
          <div class="titulo-paso">Información General</div>

          <label for="nombre_completo">Nombre completo</label>
          <input type="text" id="nombre_completo" name="nombre_completo" placeholder="Nombre completo" required>

          <label for="dni">DNI</label>
          <input type="text" id="dni" name="dni" placeholder="Ej: 0801-1999-12345" required oninput="validarDNI()" onblur="validarDNI()">
          <small id="dni-error" class="mensaje-error"></small>

          <label for="email_trabajo">Correo institucional</label>
          <input type="email" id="email_trabajo" name="email_trabajo" placeholder="Correo institucional">

          <label for="telefono">Teléfono</label>
          <input type="text" id="telefono" name="telefono" placeholder="+504 9999-9999">

          <label for="direccion">Dirección</label>
          <input type="text" id="direccion" name="direccion" placeholder="Dirección">

          <label for="selectMunicipio">Municipio</label>
          <select name="cod_municipio" id="selectMunicipio" required>
            <option value="">Seleccione municipio</option>
          </select>
        </div>
      </div>

      <!-- PASO 2: INFORMACIÓN PERSONAL -->
      <div class="paso" id="paso2">
        <div class="resumen-empleado">
          <div class="titulo-paso">Información Personal</div>

          <label for="selectGenero">Género</label>
          <select name="genero" id="selectGenero" required>
            <option value="">Seleccione género</option>
          </select>

          <label for="selectEstadoCivil">Estado civil</label>
          <select name="estado_civil" id="selectEstadoCivil" required>
            <option value="">Seleccione estado civil</option>
          </select>

          <label for="fec_nacimiento">Fecha de nacimiento</label>
          <input type="date" id="fec_nacimiento" name="fec_nacimiento" required>

          <label for="lugar_nacimiento">Lugar de nacimiento</label>
          <input type="text" id="lugar_nacimiento" name="lugar_nacimiento" placeholder="Lugar de nacimiento" required>

          <label for="nacionalidad">Nacionalidad</label>
          <input type="text" id="nacionalidad" name="nacionalidad" placeholder="Nacionalidad" required>

          <label for="nombre_contacto_emergencia">Nombre del contacto de emergencia</label>
          <input type="text" id="nombre_contacto_emergencia" name="nombre_contacto_emergencia" placeholder="Contacto emergencia" required>

          <label for="telefono_emergencia">Teléfono del contacto de emergencia</label>
          <input type="text" id="telefono_emergencia" name="telefono_emergencia" placeholder="+504 9999-9999" required>
        </div>
      </div>

      <!-- PASO 3: INFORMACIÓN LABORAL -->
      <div class="paso" id="paso3">
        <div class="resumen-empleado">
          <div class="titulo-paso">Información Laboral</div>

          <label for="selectModalidad">Modalidad de contratación</label>
          <select name="cod_tipo_modalidad" id="selectModalidad" required>
            <option value="">Seleccione modalidad</option>
          </select>

          <label for="selectPuesto">Puesto</label>
          <select name="cod_puesto" id="selectPuesto" required>
            <option value="">Seleccione puesto</option>
          </select>

          <label for="selectTipoEmpleado">Tipo de empleado</label>
          <select name="cod_tipo_empleado" id="selectTipoEmpleado" required>
            <option value="">Seleccione tipo de empleado</option>
          </select>

          <label for="selectNivelEducativo">Nivel educativo</label>
          <select name="cod_nivel_educativo" id="selectNivelEducativo" required>
            <option value="">Seleccione nivel educativo</option>
          </select>

          <label for="selectOficina">Oficina</label>
          <select name="cod_oficina" id="selectOficina" required>
            <option value="">Seleccione oficina</option>
          </select>

          <label for="selectHorario">Horario laboral</label>
          <select name="cod_horario" id="selectHorario" required>
            <option value="">Seleccione horario</option>
          </select>

          <label for="fecha_contratacion">Fecha de contratación</label>
          <input type="date" id="fecha_contratacion" name="fecha_contratacion" placeholder="Fecha contratación">

          <label for="fecha_inicio_contrato">Fecha de inicio de contrato</label>
          <input type="date" id="fecha_inicio_contrato" name="fecha_inicio_contrato" required>

          <label for="fecha_final_contrato">Fecha final de contrato</label>
          <input type="date" id="fecha_final_contrato" name="fecha_final_contrato">

          <label for="contrato_activo">Estado del contrato</label>
          <select name="contrato_activo" id="contrato_activo" required>
            <option value="true" selected>Activo</option>
            <option value="false">No activo</option>
          </select>

          <label for="salario">Salario</label>
          <input type="text" id="salario" placeholder="L. 0.00">
          <input type="hidden" name="salario" id="salario_real">

          <label for="foto">Fotografía</label>
          <input type="file" id="foto" name="foto" accept="image/*">
        </div>
      </div>

      <!-- PASO 4: RESUMEN -->
      <div class="paso" id="paso4">
        <h4>Resumen</h4>
        <div id="resumenEmpleado" class="resumen-empleado">
          <div class="resumen-foto">
            <img id="resumen-foto" src="" alt="Foto del empleado">
          </div>
          <h4 id="resumen-nombre"></h4>
          <p id="resumen-dni" class="resumen-dni"></p>
          
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

      <!-- BOTONES DE NAVEGACIÓN -->
      <div class="wizard-buttons">
        <button type="button" id="btnAnterior" onclick="anteriorPaso()">Anterior</button>
        <button type="button" id="btnSiguiente" onclick="siguientePaso()">Siguiente</button>
        <button type="submit" id="btnGuardar" style="display: none;">Guardar</button>
        <button type="button" onclick="cerrarModalRegistro()">Cancelar</button>
      </div>
    </form>
  </div>
</div>


<!-- SCRIPTS JAVASCRIPT -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

// VARIABLES GLOBALES

let modoEdicion = false;
let empleadoEditandoId = null;
let pasoActual = 1;


// FUNCIONES DE VALIDACIÓN DE CAMPOS

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

/**
 * Valida si un DNI ya existe en el sistema
 */
async function validarDNI() {
  const dniInput = document.getElementById('dni');
  const mensaje = document.getElementById('dni-error');
  const dni = dniInput.value.trim();

  const camposBloqueados = document.querySelectorAll(
    '#formulario-empleado input:not(#dni), #formulario-empleado select'
  );

  // Restaurar estado inicial
  dniInput.placeholder = 'Ej: 0801-1999-12345';
  mensaje.style.display = 'none';
  mensaje.textContent = '';

  if (dni === '') {
    dniInput.classList.remove('error');
    camposBloqueados.forEach(el => el.disabled = false);
    return;
  }

  try {
    const response = await fetch(`http://localhost:3000/api/personas/dni/${encodeURIComponent(dni)}`);
    const data = await response.json();

    if (response.ok && data.existe) {
      // Mostrar mensaje de error
      dniInput.classList.add('error');
      dniInput.value = '';
      dniInput.placeholder = '⚠️ DNI ya registrado';
      camposBloqueados.forEach(el => el.disabled = true);
    } else {
      dniInput.classList.remove('error');
      mensaje.style.display = 'none';
      camposBloqueados.forEach(el => el.disabled = false);
    }
  } catch (error) {
    console.error('Error al verificar el DNI:', error);
    dniInput.classList.add('error');
    mensaje.textContent = 'Error al verificar el DNI.';
    mensaje.style.display = 'block';
    camposBloqueados.forEach(el => el.disabled = true);
  }
}


// EVENT LISTENERS PARA VALIDACIÓN DE CAMPOS

// Nombre completo en mayúsculas
document.getElementById('nombre_completo').addEventListener('input', (e) => {
  e.target.value = e.target.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ ]/g, '');
});

// Dirección en mayúsculas
document.getElementById('direccion').addEventListener('input', (e) => {
  e.target.value = e.target.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ0-9 #.,-]/g, '');
});

// Formato DNI (0000-0000-00000)
document.getElementById('dni').addEventListener('input', (e) => {
  let val = e.target.value.replace(/\D/g, '');
  if (val.length > 13) val = val.slice(0, 13);
  let formatted = val;
  if (val.length > 4) formatted = val.slice(0, 4) + '-' + val.slice(4);
  if (val.length > 8) formatted = formatted.slice(0, 9) + '-' + val.slice(8);
  e.target.value = formatted;
});

// Teléfono con formato +504 XXXX-XXXX
document.getElementById('telefono').addEventListener('input', (e) => {
  let val = e.target.value.replace(/\D/g, '');
  if (!val.startsWith('504')) val = '504' + val;
  if (val.length > 11) val = val.slice(0, 11);
  const formatted = '+504 ' + val.slice(3, 7) + '-' + val.slice(7, 11);
  e.target.value = formatted.trim();
});

// Lugar de nacimiento en mayúsculas
document.getElementById('lugar_nacimiento').addEventListener('input', (e) => {
  e.target.value = e.target.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ ,.]/g, '');
});

// Nacionalidad en mayúsculas
document.getElementById('nacionalidad').addEventListener('input', (e) => {
  e.target.value = e.target.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ ]/g, '');
});

// Nombre contacto emergencia en mayúsculas
document.getElementById('nombre_contacto_emergencia').addEventListener('input', (e) => {
  e.target.value = e.target.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ ]/g, '');
});

// Teléfono emergencia formato +504 XXXX-XXXX
document.getElementById('telefono_emergencia').addEventListener('input', (e) => {
  let val = e.target.value.replace(/\D/g, '');
  if (!val.startsWith('504')) val = '504' + val;
  if (val.length > 11) val = val.slice(0, 11);
  const formatted = '+504 ' + val.slice(3, 7) + '-' + val.slice(7, 11);
  e.target.value = formatted.trim();
});

// Formateo de salario (L. XX,XXX.XX)
document.getElementById('salario').addEventListener('input', (e) => {
  let valor = e.target.value.replace(/[^\d]/g, ''); // Solo dígitos
  const salarioRealInput = document.getElementById('salario_real');

  if (valor === '') {
    e.target.value = '';
    salarioRealInput.value = '';
    return;
  }

  // Mostrar con formato: L. 25,000.00
  let formateado = Number(valor / 100).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

  e.target.value = `L. ${formateado}`;
  salarioRealInput.value = (valor / 100).toFixed(2);
});

// MANEJO DEL MODAL Y NAVEGACIÓN POR PASOS

function mostrarPaso(n) {
  pasoActual = n;

  // Mostrar solo el paso correspondiente
  document.querySelectorAll('.paso').forEach((p, i) => {
    p.style.display = (i + 1 === n) ? 'block' : 'none';
  });

  // Resaltar pasos completados
  document.querySelectorAll('.paso-item').forEach((item, i) => {
    item.classList.remove('activo');
    if (i < n) item.classList.add('activo');
  });

  // Control de botones
  document.getElementById('btnAnterior').style.display = (n === 1) ? 'none' : 'inline-block';
  document.getElementById('btnSiguiente').style.display = (n === 4) ? 'none' : 'inline-block';
  document.getElementById('btnGuardar').style.display = (n === 4) ? 'inline-block' : 'none';

  // Generar resumen al llegar al último paso
  if (n === 4) generarResumen();

  // Scroll al inicio del modal
  const modalContent = document.querySelector('#modalRegistroEmpleado .modal-content');
  if (modalContent) modalContent.scrollTop = 0;
}

/**
 * Navega al siguiente paso del formulario
 */
function siguientePaso() {
  if (pasoActual < 4) mostrarPaso(pasoActual + 1);
}

/**
 * Navega al paso anterior del formulario
 */
function anteriorPaso() {
  if (pasoActual > 1) mostrarPaso(pasoActual - 1);
}

/**
 * Cierra el modal de registro/edición
 */
function cerrarModalRegistro() {
  document.getElementById('modalRegistroEmpleado').style.display = 'none';
  modoEdicion = false;
  empleadoEditandoId = null;
  document.getElementById('formRegistroEmpleado').reset();
}

// Abrir modal al hacer clic en "Nuevo Empleado"
document.getElementById('btnMostrarModalEmpleado')?.addEventListener('click', () => {
  document.getElementById('modalRegistroEmpleado').style.display = 'flex';
  mostrarPaso(1);
  cargarDatosSelects();
});

// Cerrar modal con botón, click fuera o ESC
document.getElementById('cerrarModalRegistro').addEventListener('click', cerrarModalRegistro);
window.addEventListener('click', (e) => {
  if (e.target === document.getElementById('modalRegistroEmpleado')) cerrarModalRegistro();
});
window.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') cerrarModalRegistro();
});


// FUNCIONES PARA CARGAR DATOS

function cargarSelect(url, selectId, nombreCampo = 'nombre', valorCampo = 'id') {
  const select = document.getElementById(selectId);
  if (!select) return Promise.resolve();

  // mientras carga
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
  // devuelve una promesa que se resuelve cuando TODOS están listos
  return Promise.all([
    cargarSelect('http://localhost:3000/api/municipios',        'selectMunicipio',    'nombre',         'cod_municipio'),
    cargarSelect('http://localhost:3000/api/generos',           'selectGenero',       'nombre',         'nombre'),
    cargarSelect('http://localhost:3000/api/estados-civiles',   'selectEstadoCivil',  'nombre',         'nombre'),
    cargarSelect('http://localhost:3000/api/modalidades',       'selectModalidad',    'nombre',         'cod_tipo_modalidad'),
    cargarSelect('http://localhost:3000/api/puestos',           'selectPuesto',       'nombre',         'cod_puesto'),
    cargarSelect('http://localhost:3000/api/niveles-educativos','selectNivelEducativo','nombre',        'cod_nivel_educativo'),
    cargarSelect('http://localhost:3000/api/oficinas',          'selectOficina',      'nombre',         'cod_oficina'),
    cargarSelect('http://localhost:3000/api/horarios',          'selectHorario',      'nombre',         'cod_horario'),
    cargarSelect('http://localhost:3000/api/tipos-empleados',   'selectTipoEmpleado', 'nom_tipo',       'cod_tipo_empleado')
  ]);
}

function getTextoSelect(id) {
  const select = document.getElementById(id);
  return select.options[select.selectedIndex]?.text || '';
}


//Genera el resumen de los datos del empleado
 
function generarResumen() {
  const form = document.getElementById('formRegistroEmpleado');
  const data = new FormData(form);

  // Foto de perfil
  const fotoInput = form.querySelector('input[name="foto"]');
  const fotoPreview = document.getElementById('resumen-foto');
  if (fotoInput?.files?.length > 0) {
    fotoPreview.src = URL.createObjectURL(fotoInput.files[0]);
  } else {
    fotoPreview.src = '/imagenes/default.png';
  }

  // Datos básicos
  document.getElementById('resumen-nombre').textContent = data.get('nombre_completo') || '';
  document.getElementById('resumen-dni').textContent = data.get('dni') || '';

  // Información general
  document.getElementById('resumen-email').textContent = data.get('email_trabajo') || '';
  document.getElementById('resumen-telefono').textContent = data.get('telefono') || '';
  document.getElementById('resumen-direccion').textContent = data.get('direccion') || '';
  document.getElementById('resumen-municipio').textContent = getTextoSelect('selectMunicipio');

  // Información personal
  document.getElementById('resumen-genero').textContent = getTextoSelect('selectGenero');
  document.getElementById('resumen-estado-civil').textContent = getTextoSelect('selectEstadoCivil');
  document.getElementById('resumen-fecha-nacimiento').textContent = data.get('fec_nacimiento') || '';
  document.getElementById('resumen-lugar-nacimiento').textContent = data.get('lugar_nacimiento') || '';
  document.getElementById('resumen-nacionalidad').textContent = data.get('nacionalidad') || '';
  document.getElementById('resumen-contacto-emergencia').textContent = data.get('nombre_contacto_emergencia') || '';
  document.getElementById('resumen-telefono-emergencia').textContent = data.get('telefono_emergencia') || '';

  // Información laboral
  document.getElementById('resumen-puesto').textContent = getTextoSelect('selectPuesto');
  document.getElementById('resumen-tipo-empleado').textContent = getTextoSelect('selectTipoEmpleado');
  document.getElementById('resumen-modalidad').textContent = getTextoSelect('selectModalidad');
  document.getElementById('resumen-horario').textContent = getTextoSelect('selectHorario');
  document.getElementById('resumen-nivel-educativo').textContent = getTextoSelect('selectNivelEducativo');
  document.getElementById('resumen-oficina').textContent = getTextoSelect('selectOficina');
  document.getElementById('resumen-fecha-contratacion').textContent = data.get('fecha_contratacion') || '';
  document.getElementById('resumen-fecha-inicio').textContent = data.get('fecha_inicio_contrato') || '';
  document.getElementById('resumen-fecha-final').textContent = data.get('fecha_final_contrato') || '';
  
  // Formateo de salario
  let salarioRaw = data.get('salario') || '';
  salarioRaw = salarioRaw.replace(/[^\d.]/g, '');
  document.getElementById('resumen-salario').textContent = salarioRaw 
    ? Number(salarioRaw).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
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
  visible.value = 'L. ' + num.toLocaleString('es-HN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  hidden.value  = num.toFixed(2);
}


// MANEJO DE EVENTOS DE BOTONES

// Confirmación para eliminar empleado
document.querySelectorAll('.form-eliminar').forEach(form => {
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const nombre = this.dataset.nombre;
    Swal.fire({
      title: '¿Estás seguro?',
      text: `El empleado "${nombre}" se eliminará permanentemente.`,
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

// Botón editar empleado
document.querySelectorAll('.btn-editar-empleado').forEach(btn => {
  btn.addEventListener('click', async () => {
    const datos = JSON.parse(btn.getAttribute('data-empleado'));
    modoEdicion = true;
    empleadoEditandoId = datos.cod_empleado;

    // Mostrar modal y arrancar en paso 1
    document.getElementById('modalRegistroEmpleado').style.display = 'flex';
    mostrarPaso(1);

    // Cargar selects y ESPERAR a que terminen
    await cargarDatosSelects();

    // Llenar campos del formulario
    const form = document.getElementById('formRegistroEmpleado');
    form.nombre_completo.value = datos.nombre_completo || '';
    form.dni.value               = datos.dni || '';
    form.email_trabajo.value     = datos.email_trabajo || '';
    form.telefono.value          = datos.telefono || '';
    form.direccion.value         = datos.direccion || '';

    // Datos personales
    form.nombre_contacto_emergencia.value = datos.nombre_contacto_emergencia || '';
    form.telefono_emergencia.value        = datos.telefono_emergencia || '';
    form.fec_nacimiento.value             = datos.fec_nacimiento?.substring(0,10) || '';
    form.lugar_nacimiento.value           = datos.lugar_nacimiento || '';
    form.nacionalidad.value               = datos.nacionalidad || '';

    // Selects (ya existen las opciones)
    document.getElementById('selectMunicipio').value       = datos.cod_municipio || '';
    document.getElementById('selectGenero').value          = datos.genero || '';
    document.getElementById('selectEstadoCivil').value     = datos.estado_civil || '';
    document.getElementById('selectModalidad').value       = datos.cod_tipo_modalidad || '';
    document.getElementById('selectPuesto').value          = datos.cod_puesto || '';
    document.getElementById('selectTipoEmpleado').value    = datos.cod_tipo_empleado || '';
    document.getElementById('selectNivelEducativo').value  = datos.cod_nivel_educativo || '';
    document.getElementById('selectOficina').value         = datos.cod_oficina || '';
    document.getElementById('selectHorario').value         = datos.cod_horario || '';
    document.getElementById('contrato_activo').value       = datos.contrato_activo ? 'true' : 'false';

    // Fechas laborales
    form.fecha_contratacion.value     = datos.fecha_contratacion?.substring(0,10) || '';
    form.fecha_inicio_contrato.value  = datos.fecha_inicio_contrato?.substring(0,10) || '';
    form.fecha_final_contrato.value   = datos.fecha_final_contrato?.substring(0,10) || '';

    // Salario visible + hidden correctamente
    setSalario(datos.salario);
  });
});


// Botón ver detalles
document.querySelectorAll('.btn-ver-detalles').forEach(btn => {
  btn.addEventListener('click', () => {
    const datos = JSON.parse(btn.getAttribute('data-empleado'));
    const contenido = document.getElementById('contenidoDetallesEmpleado');

    // Formatear salario
    const salarioFormateado = datos.salario
      ? 'L. ' + Number(datos.salario).toLocaleString('es-HN', { minimumFractionDigits: 2 })
      : '-';

    contenido.innerHTML = `
      <div class="perfil-header">
        <img src="/img/${datos.foto_persona || 'default-user.png'}" alt="Foto del empleado" class="foto-perfil-cuadrada-resumen">
        <h3 class="nombre-empleado-resumen">${datos.nombre_completo || '-'}</h3>
        <p class="dni-empleado-resumen">${datos.dni || '-'}</p>
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

// Cerrar modal de detalles
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


// ENVÍO DEL FORMULARIO

document.getElementById('formRegistroEmpleado').addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);
  const url = modoEdicion
    ? `http://localhost:3000/api/empleados/${empleadoEditandoId}`
    : 'http://localhost:3000/api/empleados';
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