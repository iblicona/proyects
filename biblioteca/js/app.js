const apiUrl = 'api.php';

// Modales de Bootstrap
const libroModalEl = document.getElementById('libroModal');
const libroModal = libroModalEl ? new bootstrap.Modal(libroModalEl) : null;

const prestamoModalEl = document.getElementById('prestamoModal');
const prestamoModal = prestamoModalEl ? new bootstrap.Modal(prestamoModalEl) : null;

// Formularios
const formLibro = document.getElementById('libroForm');
const formPrestamo = document.getElementById('prestamoForm');

document.addEventListener('DOMContentLoaded', () => {
    cargarLibros();
    if (userRol === 'admin') {
        cargarSolicitudes(); // Solo el admin ve la bandeja de solicitudes
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

// VISTA ADMINISTRADOR (Tabla con fechas y CRUD)
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

// VISTA USUARIO (Agrupado por géneros con bloqueo de solicitud duplicada)
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

            // LÓGICA DE BOTONES PARA USUARIO
            if (l.prestado == 1) {
                // Caso 1: El libro ya lo tiene alguien físicamente
                btn = '<span class="badge bg-secondary">No disponible</span>';
            } else if (l.ya_solicitado > 0) {
                // Caso 2: El usuario ya envió solicitud y el admin no ha respondido
                btn = '<button class="btn btn-sm btn-warning" disabled>⏳ Solicitud enviada</button>';
            } else {
                // Caso 3: Disponible para pedir
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
                <summary class="p-3 fw-bold" style="cursor:pointer; list-style:none;">
                    📂 Género: ${genero.toUpperCase()} 
                    <span class="badge bg-dark float-end">${grupos[genero].length} libros</span>
                </summary>
                <div class="p-3 bg-light">
                    ${itemsHtml}
                </div>
            </details>`;
    }
}

// --- LOGICA DE FORMULARIOS (CRUD ADMIN) ---
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
    if(confirm('¿Eliminar este libro definitivamente del sistema?')) {
        const res = await fetch(`${apiUrl}?id=${id}`, { 
            method: 'DELETE' 
        });
        if(res.ok) cargarLibros();
    }
}

// --- LOGICA DE PRÉSTAMOS Y SOLICITUDES ---
function abrirModalPrestamo(id) {
    document.getElementById('prestamoLibroId').value = id;
    prestamoModal.show();
}

if(formPrestamo) {
    formPrestamo.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btnSubmit = e.target.querySelector('button[type="submit"]');
        
        // Bloqueo temporal para evitar envíos múltiples
        btnSubmit.disabled = true;
        btnSubmit.innerText = "Enviando...";

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
        btnSubmit.disabled = false;
        btnSubmit.innerText = "Enviar Solicitud";
        
        alert("Solicitud enviada. Se ha bloqueado el botón de este libro hasta que el admin autorice.");
        cargarLibros();
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
        let estadoLabel = '';

        if (s.estado === 'pendiente') {
            estadoLabel = '<span class="badge bg-warning text-dark">Pendiente</span>';
            botones = `
                <button class="btn btn-sm btn-success w-100" onclick="decidirSolicitud(${s.id}, ${s.id_libro}, 'aprobar')">Aprobar</button>
                <button class="btn btn-sm btn-danger w-100" onclick="decidirSolicitud(${s.id}, ${s.id_libro}, 'rechazar')">Rechazar</button>`;
        } else {
            estadoLabel = '<span class="badge bg-success">En Préstamo</span>';
            botones = `<button class="btn btn-sm btn-info w-100" onclick="marcarDevolucion(${s.id}, ${s.id_libro})">Marcar Devuelto</button>`;
        }

        div.innerHTML += `
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm ${s.estado === 'aprobado' ? 'border-success' : ''}">
                    <div class="card-body">
                        <h6>${s.libro}</h6>
                        <p class="small mb-2">
                            <strong>Usuario:</strong> ${s.usuario}<br>
                            <strong>Días:</strong> ${s.dias_solicitados}<br>
                            <strong>Estado:</strong> ${estadoLabel}
                        </p>
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
}

async function marcarDevolucion(idS, idL) {
    if(confirm('¿Confirmas que el libro ha sido devuelto físicamente?')) {
        await fetch(apiUrl, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                id_solicitud: idS, 
                id_libro: idL, 
                accion: 'devolver' 
            })
        });
        cargarSolicitudes();
        cargarLibros();
    }
}

// --- INTERFAZ ---
function abrirModal() {
    formLibro.reset();
    document.getElementById('libroId').value = '';
    document.getElementById('modalTitulo').innerText = 'Registrar Nuevo Libro';
    libroModal.show();
}