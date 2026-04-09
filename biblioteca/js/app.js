const apiUrl = 'api.php';

// Modales de Bootstrap
const libroModalEl = document.getElementById('libroModal');
const libroModal = libroModalEl ? new bootstrap.Modal(libroModalEl) : null;

const prestamoModalEl = document.getElementById('prestamoModal');
const prestamoModal = prestamoModalEl ? new bootstrap.Modal(prestamoModalEl) : null;

// Formularios
const formLibro = document.getElementById('libroForm');
const formPrestamo = document.getElementById('prestamoForm');

// Datos de sesión (Desde localStorage para index.html)
const userRol = localStorage.getItem('rol') || 'user';
const userName = localStorage.getItem('nombre') || 'Usuario';

document.addEventListener('DOMContentLoaded', () => {
    // Protección básica: Si no hay ID de usuario, al login
    if (!localStorage.getItem('user_id')) {
        window.location.href = 'login.php';
        return;
    }

    // Cargar interfaz inicial
    cargarLibros();
    cargarHistorial();

    if (userRol === 'admin') {
        cargarSolicitudes();
    }
});

// --- FUNCIÓN PRINCIPAL: CARGAR LIBROS ---
async function cargarLibros() {
    try {
        const res = await fetch(apiUrl);
        const libros = await res.json();
        
        if (userRol === 'admin') {
            renderizarTablaAdmin(libros);
        } else {
            renderizarGruposUsuario(libros);
        }
    } catch (e) { 
        console.error("Error cargando libros:", e); 
    }
}

// VISTA ADMINISTRADOR
function renderizarTablaAdmin(libros) {
    const tabla = document.getElementById('tabla-libros');
    if(!tabla) return;
    tabla.innerHTML = '';

    libros.forEach(l => {
        let badge = (l.prestado == 1) 
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
                    <button class="btn btn-sm btn-outline-warning" onclick='prepararEdicion(${JSON.stringify(l)})'>Editar</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="eliminarLibro(${l.id})">Borrar</button>
                </td>
            </tr>`;
    });
}

// VISTA USUARIO (Agrupado por géneros)
function renderizarGruposUsuario(libros) {
    const contenedor = document.getElementById('contenedor-generos');
    if(!contenedor) return;
    contenedor.innerHTML = '';

    const grupos = {};
    libros.forEach(l => {
        const g = (l.genero && l.genero.trim() !== '') ? l.genero : 'Otros';
        if (!grupos[g]) grupos[g] = [];
        grupos[g].push(l);
    });

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
                        <strong>${l.titulo}</strong> <br>
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
                <div class="p-3 bg-light">
                    ${itemsHtml}
                </div>
            </details>`;
    }
}

// --- HISTORIAL DE PRÉSTAMOS ---
async function cargarHistorial() {
    try {
        const res = await fetch(apiUrl + '?historial=1');
        const datos = await res.json();
        const tabla = document.getElementById('tabla-historial');
        if(!tabla) return;
        tabla.innerHTML = '';

        datos.forEach(h => {
            let colorEstado = '';
            switch(h.estado) {
                case 'aprobado': colorEstado = 'text-success'; break;
                case 'pendiente': colorEstado = 'text-warning'; break;
                case 'rechazado': colorEstado = 'text-danger'; break;
                case 'devuelto': colorEstado = 'text-primary'; break;
            }

            let colUsuario = (userRol === 'admin') ? `<td>${h.usuario}</td>` : '';
            let fEntrega = h.fecha_entrega ? h.fecha_entrega : '<span class="text-muted">-</span>';

            tabla.innerHTML += `
                <tr>
                    <td><strong>${h.libro}</strong></td>
                    ${colUsuario}
                    <td><small>${h.fecha_solicitud}</small></td>
                    <td>${h.dias_solicitados} días</td>
                    <td class="fw-bold ${colorEstado}">${h.estado.toUpperCase()}</td>
                    <td>${fEntrega}</td>
                </tr>`;
        });
    } catch (e) { console.error("Error historial:", e); }
}

// --- CRUD LIBROS ---
if(formLibro) {
    formLibro.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;

        const data = {
            id: document.getElementById('libroId').value,
            titulo: document.getElementById('titulo').value,
            autor: document.getElementById('autor').value,
            anio: document.getElementById('anio').value,
            genero: document.getElementById('genero').value,
            editorial: document.getElementById('editorial').value
        };
        
        await fetch(apiUrl, { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data) 
        });
        
        btn.disabled = false;
        libroModal.hide();
        cargarLibros();
    });
}

function prepararEdicion(l) {
    document.getElementById('libroId').value = l.id;
    document.getElementById('titulo').value = l.titulo;
    document.getElementById('autor').value = l.autor;
    document.getElementById('anio').value = l.anio;
    document.getElementById('genero').value = l.genero || '';
    document.getElementById('editorial').value = l.editorial || '';
    document.getElementById('modalTitulo').innerText = 'Editando: ' + l.titulo;
    libroModal.show();
}

async function eliminarLibro(id) {
    if(confirm('¿Eliminar este libro definitivamente?')) {
        await fetch(`${apiUrl}?id=${id}`, { method: 'DELETE' });
        cargarLibros();
    }
}

// --- SOLICITUDES Y PRÉSTAMOS ---
function abrirModalPrestamo(id) {
    document.getElementById('prestamoLibroId').value = id;
    prestamoModal.show();
}

if(formPrestamo) {
    formPrestamo.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;

        const data = {
            id_libro: document.getElementById('prestamoLibroId').value,
            dias_prestamo: document.getElementById('diasPrestamo').value
        };
        
        await fetch(apiUrl, { 
            method: 'PATCH', 
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data) 
        });
        
        prestamoModal.hide();
        btn.disabled = false;
        cargarLibros();
        cargarHistorial();
        alert("Solicitud enviada.");
    });
}

async function cargarSolicitudes() {
    const res = await fetch(apiUrl + '?solicitudes=1');
    const solicitudes = await res.json();
    const div = document.getElementById('lista-solicitudes');
    if(!div) return;
    div.innerHTML = '';

    solicitudes.forEach(s => {
        let botones = '';
        if (s.estado === 'pendiente') {
            botones = `
                <button class="btn btn-sm btn-success w-100" onclick="decidirSolicitud(${s.id}, ${s.id_libro}, 'aprobar')">Aprobar</button>
                <button class="btn btn-sm btn-danger w-100" onclick="decidirSolicitud(${s.id}, ${s.id_libro}, 'rechazar')">Rechazar</button>`;
        } else {
            botones = `<button class="btn btn-sm btn-info w-100" onclick="marcarDevolucion(${s.id}, ${s.id_libro})">Marcar Devuelto</button>`;
        }

        div.innerHTML += `
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6>${s.libro}</h6>
                        <p class="small mb-2"><strong>Usuario:</strong> ${s.usuario}<br><strong>Días:</strong> ${s.dias_solicitados}</p>
                        <div class="d-flex gap-2">${botones}</div>
                    </div>
                </div>
            </div>`;
    });
}

async function decidirSolicitud(idS, idL, accion) {
    await fetch(apiUrl, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_solicitud: idS, id_libro: idL, accion: accion })
    });
    cargarSolicitudes();
    cargarLibros();
    cargarHistorial();
}

async function marcarDevolucion(idS, idL) {
    if(confirm('¿Marcar como devuelto físicamente?')) {
        await fetch(apiUrl, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_solicitud: idS, id_libro: idL, accion: 'devolver' })
        });
        cargarSolicitudes();
        cargarLibros();
        cargarHistorial();
    }
}

function abrirModal() {
    formLibro.reset();
    document.getElementById('libroId').value = '';
    document.getElementById('modalTitulo').innerText = 'Registrar Nuevo Libro';
    libroModal.show();
}