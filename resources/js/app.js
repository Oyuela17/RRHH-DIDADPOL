import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';
import esLocale from '@fullcalendar/core/locales/es';
import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  const modal = document.getElementById('modalEvento');
  const form = document.getElementById('formEvento');
  const modalContenido = modal?.querySelector('.modal-contenido');
  const btnEliminar = document.getElementById('eliminarEvento');

  // === CSRF de Laravel ===
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  // === Helpers de fecha ===
  const pad = (n) => String(n).padStart(2, '0');
  const ymd = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  function toLocalDatetimeValue(d) {
    const dt = (d instanceof Date) ? d : new Date(d);
    return `${ymd(dt)}T${pad(dt.getHours())}:${pad(dt.getMinutes())}`;
  }
  // FullCalendar usa end exclusivo en allDay; lo convertimos a inclusivo (23:59 del día anterior)
  function allDayEndInclusive(endExclusive) {
    const dt = new Date(endExclusive.getTime() - 1); // 1 ms antes
    return `${ymd(dt)}T23:59`;
  }

  const calendar = new Calendar(calendarEl, {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin, listPlugin],
    locale: esLocale,
    initialView: 'dayGridMonth',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
    },
    selectable: true,
    editable: true,

    // === Cargar eventos (desde Laravel, que a su vez llama a Node con el empleado) ===
    events: async function (fetchInfo, successCallback, failureCallback) {
      try {
        const response = await fetch('/calendario/obtener-eventos', {
          headers: { 'Accept': 'application/json' },
          credentials: 'same-origin'
        });
        const data = await response.json();

        const eventos = data.map(evento => ({
          id: evento.id,
          title: evento.titulo,
          start: evento.fecha_inicio, // ISO o parseable por Date
          end: evento.fecha_fin || null,
          allDay: (evento.todo_el_dia === true || evento.todo_el_dia === 'true') || false,
          backgroundColor: evento.color_fondo || '#007bff',
          textColor: evento.color_texto || '#ffffff',
          extendedProps: {
            descripcion: evento.descripcion,
            lugar: evento.lugar,
            tipo: evento.tipo,
            enlace: evento.enlace,
            recurrente: evento.recurrente
            // No exponemos cod_empleado en el front
          }
        }));

        successCallback(eventos);
      } catch (error) {
        console.error('Error al obtener eventos:', error);
        failureCallback(error);
      }
    },

    eventContent: function (arg) {
      const dot = document.createElement('span');
      dot.classList.add('event-dot');
      dot.style.backgroundColor = arg.event.backgroundColor;

      const title = document.createElement('span');
      title.innerText = ' ' + arg.event.title;
      title.style.fontWeight = 'bold';
      title.style.color = '#000';

      return { domNodes: [dot, title] };
    },

    // === Crear (click en fecha) ===
    dateClick: function (info) {
      limpiarFormulario();
      document.getElementById('fecha_inicio').value = `${info.dateStr}T08:00`;
      document.getElementById('fecha_fin').value = `${info.dateStr}T09:00`;
      document.getElementById('todo_el_dia').value = 'false';
      document.getElementById('tituloModal').innerText = 'Nuevo Evento';
      btnEliminar.style.display = 'none';
      modal.style.display = 'flex';
    },

    // === Editar (click en evento) ===
    eventClick: function (info) {
      const e = info.event;
      const xp = e.extendedProps;

      const allDay = e.allDay === true;
      let inicioVal = '';
      let finVal = '';

      if (e.start) {
        inicioVal = allDay ? `${ymd(e.start)}T00:00` : toLocalDatetimeValue(e.start);
      }
      if (e.end) {
        finVal = allDay ? allDayEndInclusive(e.end) : toLocalDatetimeValue(e.end);
      } else {
        finVal = allDay ? `${ymd(e.start)}T23:59` : '';
      }

      document.getElementById('evento_id').value = e.id;
      document.getElementById('titulo').value = e.title || '';
      document.getElementById('fecha_inicio').value = inicioVal;
      document.getElementById('fecha_fin').value = finVal;
      document.getElementById('descripcion').value = xp.descripcion || '';
      document.getElementById('lugar').value = xp.lugar || '';
      document.getElementById('color_fondo').value = e.backgroundColor || '#007bff';
      document.getElementById('tipo').value = xp.tipo || '';
      document.getElementById('enlace').value = xp.enlace || '';
      document.getElementById('recurrente').value = (xp.recurrente === true || xp.recurrente === 'true') ? 'true' : 'false';
      document.getElementById('todo_el_dia').value = allDay ? 'true' : 'false';

      document.getElementById('tituloModal').innerText = 'Editar Evento';
      btnEliminar.style.display = 'inline-block';
      modal.style.display = 'flex';
    }
  });

  calendar.render();

  // === Guardar (crear/actualizar) vía Laravel
  form?.addEventListener('submit', async function (e) {
    e.preventDefault();

    const id = document.getElementById('evento_id').value;
    const todoElDia = document.getElementById('todo_el_dia').value === 'true';

    const data = {
      titulo: document.getElementById('titulo').value,
      fecha_inicio: document.getElementById('fecha_inicio').value, // YYYY-MM-DDTHH:MM
      fecha_fin: document.getElementById('fecha_fin').value || null,
      todo_el_dia: todoElDia,
      descripcion: document.getElementById('descripcion').value,
      lugar: document.getElementById('lugar').value,
      color_fondo: document.getElementById('color_fondo').value,
      color_texto: '#ffffff',
      tipo: document.getElementById('tipo').value,
      enlace: document.getElementById('enlace').value,
      recurrente: document.getElementById('recurrente').value === 'true'
      // NO enviamos cod_empleado; lo pone Laravel -> Node
    };

    const url = id
      ? `/calendario/actualizar-evento/${id}`
      : `/calendario/guardar-evento`;
    const method = id ? 'PUT' : 'POST';

    try {
      const response = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF
        },
        credentials: 'same-origin',
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (response.ok) {
        Swal.fire({
          icon: 'success',
          title: id ? 'Evento actualizado' : 'Evento creado',
          timer: 1500,
          showConfirmButton: false
        });
        modal.style.display = 'none';
        calendar.refetchEvents();
      } else {
        throw new Error(result.error || 'Error inesperado');
      }
    } catch (error) {
      Swal.fire('Error', error.message, 'error');
    }
  });

  // === Eliminar vía Laravel
  btnEliminar?.addEventListener('click', async function () {
    const id = document.getElementById('evento_id').value;
    if (!id) return;

    const confirmacion = await Swal.fire({
      title: '¿Eliminar evento?',
      text: 'Esta acción no se puede deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    });

    if (confirmacion.isConfirmed) {
      try {
        const response = await fetch(`/calendario/eliminar-evento/${id}`, {
          method: 'DELETE',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF
          },
          credentials: 'same-origin'
        });

        if (response.ok) {
          Swal.fire('Eliminado', 'El evento ha sido eliminado.', 'success');
          modal.style.display = 'none';
          calendar.refetchEvents();
        } else {
          const res = await response.json().catch(() => ({}));
          throw new Error(res.error || 'No se pudo eliminar el evento.');
        }
      } catch (error) {
        Swal.fire('Error', error.message, 'error');
      }
    }
  });

  // === Cierres del modal
  document.getElementById('cancelarEvento')?.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') modal.style.display = 'none';
  });

  modal?.addEventListener('click', function (e) {
    if (!modalContenido.contains(e.target)) {
      modal.style.display = 'none';
    }
  });

  function limpiarFormulario() {
    form.reset();
    document.getElementById('evento_id').value = '';
    document.getElementById('color_fondo').value = '#007bff';
    document.getElementById('recurrente').value = 'false';
    document.getElementById('todo_el_dia').value = 'false';
    btnEliminar.style.display = 'none';
  }
  // ====== Recordatorios de eventos (15 min antes) ======
const TOAST_WRAP = document.getElementById('toastWrap');
const LEAD_MINUTES = 15; // ventana de aviso (0-15 min antes)
const NOTI_KEY = () => `cal_noti_${new Date().toISOString().slice(0,10)}`; // por día

// pide permiso una vez para notificaciones del sistema
if ('Notification' in window && Notification.permission === 'default') {
  Notification.requestPermission().catch(()=>{});
}

function minutesDiff(from, to) {
  return Math.round((to - from) / (1000*60));
}

function loadShownSet() {
  try { return new Set(JSON.parse(localStorage.getItem(NOTI_KEY()) || '[]')); }
  catch { return new Set(); }
}
function saveShownSet(set) {
  localStorage.setItem(NOTI_KEY(), JSON.stringify([...set]));
}

function showToast({ title, when, color }) {
  const el = document.createElement('div');
  el.className = 'toast';
  el.innerHTML = `
    <span class="badge" style="background:${color || '#2563eb'}"></span>
    <div>
      <div class="title">${title}</div>
      <div class="meta">Empieza a las ${when}</div>
    </div>
    <button class="close" aria-label="Cerrar">&times;</button>
  `;
  el.querySelector('.close').addEventListener('click', ()=> el.remove());
  TOAST_WRAP.appendChild(el);
  setTimeout(() => el.remove(), 1000 * 10); // se auto-cierra a los 10s
}

function showNotification({ title, when }) {
  if ('Notification' in window && Notification.permission === 'granted') {
    try { new Notification(title, { body: `Empieza a las ${when}` }); } catch {}
  } else {
    showToast({ title, when });
  }
}

function checkUpcomingReminders() {
  const now = new Date();
  const shown = loadShownSet();

  // toma los eventos del calendario
  const events = calendar.getEvents();
  for (const e of events) {
    if (!e.start) continue;

    // solo eventos de HOY
    const isToday =
      e.start.getFullYear() === now.getFullYear() &&
      e.start.getMonth() === now.getMonth() &&
      e.start.getDate() === now.getDate();

    if (!isToday) continue;

    // diferencia en minutos hasta el inicio
    const diff = minutesDiff(now, e.start);
    if (diff < 0 || diff > LEAD_MINUTES) continue; // fuera de ventana

    // evita duplicados
    const key = `${e.id || e.title}-${e.start.toISOString()}`;
    if (shown.has(key)) continue;

    const hh = String(e.start.getHours()).padStart(2,'0');
    const mm = String(e.start.getMinutes()).padStart(2,'0');

    showNotification({
      title: e.title || 'Evento',
      when: `${hh}:${mm}`,
      color: e.backgroundColor
    });

    shown.add(key);
  }
  saveShownSet(shown);
}

// lanza el chequeo cada minuto y uno inicial al renderizar
setInterval(checkUpcomingReminders, 60 * 1000);
checkUpcomingReminders();

});
