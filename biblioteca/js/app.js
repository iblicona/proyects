// ============================================================
// js/app.js — Frontend principal del sistema de biblioteca
// ============================================================

const apiUrl = 'api.php';

// Variables globales de sesión
const userRol  = localStorage.getItem('rol')     || 'user';
const userName = localStorage.getItem('nombre')  || 'Usuario';
const userId   = localStorage.getItem('user_id');

// Modales y formularios — se inicializan en DOMContentLoaded
let libroModal   = null;
let prestamoModal = null;
const formLibro   = document.getElementById('libroForm');
const formPrestamo = document.getElementById('prestamoForm');

// Headers que se mandan en CADA petición fetch
function getHeaders() {
    return {
        'Content-Type': 'application/json',
        'X-User-Id':  userId  || '',
        'X-User-Rol': userRol || ''
    };
}

// ============================================================
document.addEventListener('DOMContentLoaded', () => {

    // Protección: si no hay user_id en localStorage, mandar al login
    if (!userId) {
        console.warn('[APP] No hay sesión en localStorage, redirigiendo...');
        window.location.href = 'index.html';
        return;
    }

    console.log(`[APP] Sesión activa — usuario: ${userName}, rol: ${userRol}, id: ${userId}`);

    // Inicializar modales AQUÍ (DOM ya cargado)
    const libroModalEl    = document.getElementById('libroModal');
    const prestamoModalEl = document.getElementById('prestamoModal');
    libroModal    = libroModalEl    ? new bootstrap.Modal(libroModalEl)    : null;
    prestamoModal = prestamoModalEl ? new bootstrap.Modal(prestamoModalEl) : null;

    if (!libroModal)    console.error('[APP] ❌ No se encontró #libroModal en el HTML');
    if (!prestamoModal) console.error('[APP] ❌ No se encontró #prestamoModal en el HTML');

    // Cargar datos
    cargarLibros();
    cargarHistorial();
    if (userRol === 'admin') cargarSolicitudes();
});

// ============================================================
// CARGAR LIBROS
// ============================================================
async function cargarLibros() {
    console.log('[APP] Cargando libros...');
    try {
        const res = await fetch(apiUrl, { headers: getHeaders() });

        // Si el servidor devuelve 401, la sesión expiró
        if (res.status === 401) {
            console.error('[APP] 401 — sesión inválida en servidor');
            localStorage.clear();
            window.location.href = 'index.html';
            return;
        }

        const data = await res.json();
        console.log('[APP] Respuesta libros:', data);

        // Si el backend devolvió un error en JSON
        if (data.error) {
            console.error('[APP] Error del servidor:', data.error);
            mostrarError('tabla-libros', data.error);
            return;
        }

        if (userRol === 'admin') {
            renderizarTablaAdmin(data);
        } else {
            renderizarGruposUsuario(data);
        }

    } catch (e) {
        console.error('[APP] fetch cargarLibros falló:', e);
    }
}

// ============================================================
// VISTA ADMIN — tabla
// ============================================================
function renderizarTablaAdmin(libros) {
    const tabla = document.getElementById('tabla-libros');
    if (!tabla) return;

    if (!libros || libros.length === 0) {
        tabla.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No hay libros registrados. Usa "+ Registrar Libro" para agregar.</td></tr>';
        return;
    }

    tabla.innerHTML = '';
    libros.forEach(l => {
        const badge = (l.prestado == 1)
            ? '<span class="status-dot bg-danger"></span> Ocupado'
            : '<span class="status-dot bg-success"></span> Disponible';

        tabla.innerHTML += `
            <tr>
                <td><strong>${l.titulo}</strong></td>
                <td>${l.autor}</td>
                <td><span class="text-muted">${l.genero || 'N/A'} / ${l.editorial || 'N/A'}</span></td>
                <td>${l.anio}</td>
                <td>${badge}</td>
                <td><small>${l.creado_en}</small></td>
                <td><small>${l.actualizado_en}</small></td>
                <td>
                    <button class="btn btn-sm btn-outline-warning me-1" onclick='prepararEdicion(${JSON.stringify(l)})'>Editar</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarLibro(${l.id})">Borrar</button>
                </td>
            </tr>`;
    });
}

// ============================================================
// VISTA USUARIO — carpetas por género
// ============================================================
function renderizarGruposUsuario(libros) {
    const contenedor = document.getElementById('contenedor-generos');
    if (!contenedor) return;

    if (!libros || libros.length === 0) {
        contenedor.innerHTML = '<p class="text-center text-muted py-4">No hay libros disponibles.</p>';
        return;
    }

    const grupos = {};
    libros.forEach(l => {
        const g = (l.genero && l.genero.trim() !== '') ? l.genero : 'Otros';
        if (!grupos[g]) grupos[g] = [];
        grupos[g].push(l);
    });

    contenedor.innerHTML = '';
    for (const genero in grupos) {
        let itemsHtml = '';
        grupos[genero].forEach(l => {
            let btn = '';
            if (l.prestado == 1) {
                btn = '<span class="badge bg-secondary">No disponible</span>';
            } else if (l.ya_solicitado > 0) {
                btn = '<button class="btn btn-sm btn-warning" disabled>⏳ Solicitud enviada</button>';
            } else {
                btn = `<button class="btn btn-sm btn-primary" onclick="abrirModalPrestamo(${l.id})">Solicitar</button>`;
            }

            itemsHtml += `
                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                    <div>
                        <strong>${l.titulo}</strong><br>
                        <small class="text-muted">Autor: ${l.autor} | Editorial: ${l.editorial || 'N/A'}</small>
                    </div>
                    ${btn}
                </div>`;
        });

        contenedor.innerHTML += `
            <details class="mb-3 border rounded shadow-sm bg-white">
                <summary class="p-3 fw-bold">
                    📂 Género: ${genero.toUpperCase()}
                    <span class="badge bg-dark float-end">${grupos[genero].length} libros</span>
                </summary>
                <div class="p-3 bg-light">${itemsHtml}</div>
            </details>`;
    }
}

// ============================================================
// HISTORIAL
// ============================================================
async function cargarHistorial() {
    console.log('[APP] Cargando historial...');
    try {
        const res  = await fetch(apiUrl + '?historial=1', { headers: getHeaders() });
        const data = await res.json();
        console.log('[APP] Historial:', data);

        const tabla = document.getElementById('tabla-historial');
        if (!tabla) return;

        if (data.error) {
            tabla.innerHTML = `<tr><td colspan="6" class="text-danger">${data.error}</td></tr>`;
            return;
        }

        if (!data || data.length === 0) {
            tabla.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Sin movimientos registrados.</td></tr>';
            return;
        }

        tabla.innerHTML = '';
        data.forEach(h => {
            const colores = { aprobado: 'text-success', pendiente: 'text-warning', rechazado: 'text-danger', devuelto: 'text-primary' };
            const color   = colores[h.estado] || '';
            const colUser = (userRol === 'admin') ? `<td>${h.usuario}</td>` : '';
            const fEntrega = h.fecha_entrega ? h.fecha_entrega : '<span class="text-muted">-</span>';

            tabla.innerHTML += `
                <tr>
                    <td><strong>${h.libro}</strong></td>
                    ${colUser}
                    <td><small>${h.fecha_solicitud}</small></td>
                    <td>${h.dias_solicitados} días</td>
                    <td class="fw-bold ${color}">${h.estado.toUpperCase()}</td>
                    <td>${fEntrega}</td>
                </tr>`;
        });
    } catch (e) {
        console.error('[APP] fetch historial falló:', e);
    }
}

// ============================================================
// CRUD — REGISTRAR / EDITAR
// ============================================================
if (formLibro) {
    formLibro.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled    = true;
        btn.textContent = 'Guardando...';

        const data = {
            id:        document.getElementById('libroId').value,
            titulo:    document.getElementById('titulo').value.trim(),
            autor:     document.getElementById('autor').value.trim(),
            anio:      document.getElementById('anio').value,
            genero:    document.getElementById('genero').value.trim(),
            editorial: document.getElementById('editorial').value.trim()
        };

        console.log('[APP] Enviando libro:', data);

        try {
            const res  = await fetch(apiUrl, { method: 'POST', headers: getHeaders(), body: JSON.stringify(data) });
            const resp = await res.json();
            console.log('[APP] Respuesta POST:', resp);

            if (resp.error) {
                alert('Error: ' + resp.error);
            } else {
                libroModal.hide();
                cargarLibros();
            }
        } catch (err) {
            console.error('[APP] Error POST libro:', err);
            alert('Error al conectar con el servidor.');
        }

        btn.disabled    = false;
        btn.textContent = 'Guardar Cambios';
    });
}

function prepararEdicion(l) {
    document.getElementById('libroId').value        = l.id;
    document.getElementById('titulo').value         = l.titulo;
    document.getElementById('autor').value          = l.autor;
    document.getElementById('anio').value           = l.anio;
    document.getElementById('genero').value         = l.genero    || '';
    document.getElementById('editorial').value      = l.editorial || '';
    document.getElementById('modalTitulo').innerText = 'Editando: ' + l.titulo;
    libroModal.show();
}

async function eliminarLibro(id) {
    if (!confirm('¿Eliminar este libro definitivamente?')) return;
    console.log('[APP] Eliminando libro id:', id);
    try {
        const res  = await fetch(`${apiUrl}?id=${id}`, { method: 'DELETE', headers: getHeaders() });
        const resp = await res.json();
        console.log('[APP] Respuesta DELETE:', resp);
        cargarLibros();
    } catch (err) {
        console.error('[APP] Error DELETE:', err);
    }
}

function abrirModal() {
    if (!libroModal) {
        console.error('[APP] libroModal es null — el DOM no cargó antes de llamar abrirModal');
        return;
    }
    formLibro.reset();
    document.getElementById('libroId').value         = '';
    document.getElementById('modalTitulo').innerText = 'Registrar Nuevo Libro';
    libroModal.show();
}

// ============================================================
// PRÉSTAMOS
// ============================================================
function abrirModalPrestamo(id) {
    document.getElementById('prestamoLibroId').value = id;
    prestamoModal.show();
}

if (formPrestamo) {
    formPrestamo.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;

        const data = {
            id_libro:      document.getElementById('prestamoLibroId').value,
            dias_prestamo: document.getElementById('diasPrestamo').value
        };

        try {
            const res  = await fetch(apiUrl, { method: 'PATCH', headers: getHeaders(), body: JSON.stringify(data) });
            const resp = await res.json();
            console.log('[APP] Respuesta PATCH préstamo:', resp);
            prestamoModal.hide();
            cargarLibros();
            cargarHistorial();
            alert('Solicitud enviada correctamente.');
        } catch (err) {
            console.error('[APP] Error PATCH préstamo:', err);
        }

        btn.disabled = false;
    });
}

// ============================================================
// SOLICITUDES (Admin)
// ============================================================
async function cargarSolicitudes() {
    console.log('[APP] Cargando solicitudes...');
    try {
        const res  = await fetch(apiUrl + '?solicitudes=1', { headers: getHeaders() });
        const data = await res.json();
        console.log('[APP] Solicitudes:', data);

        const div = document.getElementById('lista-solicitudes');
        if (!div) return;

        if (!data || data.length === 0) {
            div.innerHTML = '<p class="text-muted text-center w-100 py-3">No hay solicitudes activas.</p>';
            return;
        }

        div.innerHTML = '';
        data.forEach(s => {
            let botones = '';
            if (s.estado === 'pendiente') {
                botones = `
                    <button class="btn btn-sm btn-success w-100 mb-1" onclick="decidirSolicitud(${s.id},${s.id_libro},'aprobar')">✅ Aprobar</button>
                    <button class="btn btn-sm btn-danger  w-100"     onclick="decidirSolicitud(${s.id},${s.id_libro},'rechazar')">❌ Rechazar</button>`;
            } else {
                botones = `<button class="btn btn-sm btn-info w-100" onclick="marcarDevolucion(${s.id},${s.id_libro})">📦 Marcar Devuelto</button>`;
            }

            div.innerHTML += `
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h6 class="mb-1">${s.libro}</h6>
                            <p class="small mb-2 text-muted">
                                <strong>Usuario:</strong> ${s.usuario}<br>
                                <strong>Días:</strong> ${s.dias_solicitados}
                            </p>
                            ${botones}
                        </div>
                    </div>
                </div>`;
        });
    } catch (err) {
        console.error('[APP] Error solicitudes:', err);
    }
}

async function decidirSolicitud(idS, idL, accion) {
    try {
        const res  = await fetch(apiUrl, {
            method: 'PUT',
            headers: getHeaders(),
            body: JSON.stringify({ id_solicitud: idS, id_libro: idL, accion })
        });
        const resp = await res.json();
        console.log('[APP] decidirSolicitud:', resp);
        cargarSolicitudes();
        cargarLibros();
        cargarHistorial();
    } catch (err) {
        console.error('[APP] Error PUT solicitud:', err);
    }
}

async function marcarDevolucion(idS, idL) {
    if (!confirm('¿Confirmar devolución física del libro?')) return;
    try {
        const res  = await fetch(apiUrl, {
            method: 'PUT',
            headers: getHeaders(),
            body: JSON.stringify({ id_solicitud: idS, id_libro: idL, accion: 'devolver' })
        });
        const resp = await res.json();
        console.log('[APP] marcarDevolucion:', resp);
        cargarSolicitudes();
        cargarLibros();
        cargarHistorial();
    } catch (err) {
        console.error('[APP] Error devolucion:', err);
    }
}

// ============================================================
// UTILIDADES
// ============================================================
function mostrarError(tbodyId, mensaje) {
    const el = document.getElementById(tbodyId);
    if (el) el.innerHTML = `<tr><td colspan="8" class="text-danger text-center py-3">⚠️ ${mensaje}</td></tr>`;
}