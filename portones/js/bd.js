// ============================================================
//  bd.js — Lógica del panel de gestión de base de datos
//  Usa api.js (enviarDatos) y crud.php como backend
// ============================================================

// ── Configuración de tablas: columnas, etiquetas y formularios ────────────
const CONFIG_TABLAS = {
    persona: {
        label: 'Personas',
        pk: 'id_persona',
        // Columnas que se muestran en la tabla (orden y etiqueta)
        columnas: [
            { key: 'id_persona',        label: 'ID'           },
            { key: 'nombre',            label: 'Nombre'       },
            { key: 'apellido_paterno',  label: 'Ap. Paterno'  },
            { key: 'apellido_materno',  label: 'Ap. Materno'  },
            { key: 'tipo_persona',      label: 'Tipo'         },
            { key: 'correo',            label: 'Correo'       },
            { key: 'telefono',          label: 'Teléfono'     },
            { key: 'matricula',         label: 'Matrícula'    },
            { key: 'estado_institucional', label: 'Estado'   },
        ],
        // Campos del formulario de creación/edición
        campos: [
            { name: 'nombre',           label: 'Nombre',           type: 'text',   required: true  },
            { name: 'apellido_paterno', label: 'Apellido Paterno', type: 'text',   required: true  },
            { name: 'apellido_materno', label: 'Apellido Materno', type: 'text',   required: false },
            { name: 'fecha_nacimiento', label: 'Fecha Nacimiento', type: 'date',   required: false },
            { name: 'telefono',         label: 'Teléfono',         type: 'text',   required: false },
            { name: 'correo',           label: 'Correo',           type: 'email',  required: false },
            { name: 'tipo_persona',     label: 'Tipo de Persona',  type: 'select', required: true,
              opciones: ['alumno','docente','administrativo','invitado'] },
        ]
    },

    alumno: {
        label: 'Alumnos',
        pk: 'id_alumno',
        columnas: [
            { key: 'id_alumno',            label: 'ID'           },
            { key: 'nombre',               label: 'Nombre'       },
            { key: 'apellido_paterno',     label: 'Ap. Paterno'  },
            { key: 'matricula',            label: 'Matrícula'    },
            { key: 'grado',                label: 'Grado'        },
            { key: 'grupo',                label: 'Grupo'        },
            { key: 'cuatrimestre',         label: 'Cuatrimestre' },
            { key: 'id_nivel',             label: 'Nivel'        },
            { key: 'estado_institucional', label: 'Estado'       },
        ],
        campos: [
            { name: 'id_persona',           label: 'ID Persona (FK)',    type: 'number', required: true  },
            { name: 'matricula',            label: 'Matrícula',           type: 'text',   required: false },
            { name: 'grado',                label: 'Grado',               type: 'number', required: false },
            { name: 'grupo',                label: 'Grupo',               type: 'text',   required: false },
            { name: 'cuatrimestre',         label: 'Cuatrimestre',        type: 'number', required: false },
            { name: 'id_nivel',             label: 'Nivel (1=Prepa,2=Uni)', type: 'number', required: true },
            { name: 'id_carrera',           label: 'ID Carrera',          type: 'number', required: false },
            { name: 'estado_institucional', label: 'Estado Institucional', type: 'select', required: true,
              opciones: ['activo','suspendido','baja','egresado'] },
        ]
    },

    docente: {
        label: 'Docentes',
        pk: 'id_docente',
        columnas: [
            { key: 'id_docente',       label: 'ID'          },
            { key: 'nombre',           label: 'Nombre'      },
            { key: 'apellido_paterno', label: 'Ap. Paterno' },
            { key: 'especialidad',     label: 'Especialidad'},
            { key: 'id_nivel',         label: 'Nivel'       },
            { key: 'correo',           label: 'Correo'      },
            { key: 'telefono',         label: 'Teléfono'    },
        ],
        campos: [
            { name: 'id_persona',   label: 'ID Persona (FK)', type: 'number', required: true  },
            { name: 'especialidad', label: 'Especialidad',    type: 'text',   required: false },
            { name: 'id_nivel',     label: 'Nivel (1=Prepa,2=Uni)', type: 'number', required: true },
        ]
    },

    administrativo: {
        label: 'Administrativos',
        pk: 'id_administrativo',
        columnas: [
            { key: 'id_administrativo', label: 'ID'           },
            { key: 'nombre',            label: 'Nombre'       },
            { key: 'apellido_paterno',  label: 'Ap. Paterno'  },
            { key: 'puesto',            label: 'Puesto'       },
            { key: 'departamento',      label: 'Departamento' },
            { key: 'correo',            label: 'Correo'       },
        ],
        campos: [
            { name: 'id_persona',   label: 'ID Persona (FK)', type: 'number', required: true  },
            { name: 'puesto',       label: 'Puesto',          type: 'text',   required: false },
            { name: 'departamento', label: 'Departamento',    type: 'text',   required: false },
        ]
    },

    asistencia: {
        label: 'Asistencias',
        pk: 'id_asistencia',
        columnas: [
            { key: 'id_asistencia',  label: 'ID'           },
            { key: 'nombre_persona', label: 'Persona'      },
            { key: 'fecha',          label: 'Fecha'        },
            { key: 'hora_entrada',   label: 'Hora Entrada' },
            { key: 'hora_salida',    label: 'Hora Salida'  },
            { key: 'tipo_registro',  label: 'Tipo'         },
        ],
        campos: [
            { name: 'id_persona',   label: 'ID Persona (FK)', type: 'number', required: true  },
            { name: 'fecha',        label: 'Fecha',           type: 'date',   required: true  },
            { name: 'hora_entrada', label: 'Hora Entrada',    type: 'time',   required: false },
            { name: 'hora_salida',  label: 'Hora Salida',     type: 'time',   required: false },
            { name: 'tipo_registro',label: 'Tipo de Registro', type: 'select', required: true,
              opciones: ['entrada','salida'] },
        ]
    },

    visita: {
        label: 'Visitas',
        pk: 'id_visita',
        columnas: [
            { key: 'id_visita',      label: 'ID'        },
            { key: 'nombre_persona', label: 'Persona'   },
            { key: 'curp',           label: 'CURP'      },
            { key: 'motivo',         label: 'Motivo'    },
        ],
        campos: [
            { name: 'id_persona', label: 'ID Persona (FK)', type: 'number', required: true  },
            { name: 'curp',       label: 'CURP',            type: 'text',   required: false },
            { name: 'motivo',     label: 'Motivo de visita',type: 'text',   required: false },
        ]
    },

    usuario: {
        label: 'Usuarios del Sistema',
        pk: 'id_usuario',
        columnas: [
            { key: 'id_usuario', label: 'ID'     },
            { key: 'username',   label: 'Usuario' },
            { key: 'rol',        label: 'Rol'     },
        ],
        campos: [
            { name: 'username', label: 'Nombre de usuario', type: 'text',     required: true  },
            { name: 'password', label: 'Contraseña',        type: 'password', required: false,
              hint: 'Dejar vacío para no cambiar (al editar)' },
            { name: 'rol',      label: 'Rol',               type: 'select',   required: true,
              opciones: ['admin','control','consulta'] },
        ]
    },

    horario_grupo: {
        label: 'Horarios de Grupos',
        pk: 'id_horario',
        columnas: [
            { key: 'id_horario',            label: 'ID'                    },
            { key: 'grupo',                 label: 'Grupo'                 },
            { key: 'hora_salida_permitida', label: 'Hora Salida Permitida' },
        ],
        campos: [
            { name: 'grupo',                 label: 'Grupo',                   type: 'text', required: true },
            { name: 'hora_salida_permitida', label: 'Hora de Salida Permitida', type: 'time', required: true },
        ]
    },
};

// ── Estado global ─────────────────────────────────────────────────────────
let tablaActual   = 'persona';
let registroActual = null; // registro en edición (null = modo creación)
let datosTabla    = [];    // caché local de los registros cargados

// ── Inicialización ────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    construirTabs();
    cargarTabla('persona');

    document.getElementById('btnNuevoRegistro').addEventListener('click', abrirModalCrear);
    document.getElementById('btnGuardarModal').addEventListener('click', guardarRegistro);
    document.getElementById('inputBusqueda').addEventListener('input', filtrarTabla);
});

// ── Construir tabs de navegación ──────────────────────────────────────────
function construirTabs() {
    const nav = document.getElementById('navTabs');
    Object.entries(CONFIG_TABLAS).forEach(([clave, cfg], i) => {
        const li = document.createElement('li');
        li.className = 'nav-item';
        li.innerHTML = `
            <button class="nav-link ${i === 0 ? 'active' : ''}"
                    data-tabla="${clave}"
                    onclick="cambiarTabla('${clave}', this)">
                ${cfg.label}
            </button>`;
        nav.appendChild(li);
    });
}

function cambiarTabla(clave, btn) {
    document.querySelectorAll('#navTabs .nav-link').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    cargarTabla(clave);
}

// ── Cargar registros desde el servidor ───────────────────────────────────
async function cargarTabla(clave) {
    tablaActual    = clave;
    registroActual = null;

    const cfg = CONFIG_TABLAS[clave];
    document.getElementById('tituloTabla').textContent  = cfg.label;
    document.getElementById('inputBusqueda').value = '';

    mostrarCargando(true);

    try {
        const res = await enviarDatos('crud.php', { accion: 'listar', tabla: clave });
        if (!res || !res.ok) {
            mostrarError(res?.mensaje || 'Error al cargar los datos.');
            return;
        }

        datosTabla = res.datos || [];
        renderizarTabla(datosTabla);
        document.getElementById('contadorRegistros').textContent = `${datosTabla.length} registros`;

    } catch (e) {
        mostrarError('No se pudo conectar con el servidor.');
    } finally {
        mostrarCargando(false);
    }
}

// ── Renderizar tabla con los datos ────────────────────────────────────────
function renderizarTabla(datos) {
    const cfg    = CONFIG_TABLAS[tablaActual];
    const thead  = document.getElementById('tablaThead');
    const tbody  = document.getElementById('tablaTbody');

    // Cabeceras
    thead.innerHTML = '<tr>' +
        cfg.columnas.map(c => `<th>${c.label}</th>`).join('') +
        '<th>Acciones</th></tr>';

    // Filas
    if (datos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="${cfg.columnas.length + 1}" class="text-center text-muted py-4">No hay registros.</td></tr>`;
        return;
    }

    tbody.innerHTML = datos.map(fila => {
        const celdas = cfg.columnas.map(c => {
            let val = fila[c.key] ?? '-';
            // Badge de color para estado_institucional
            if (c.key === 'estado_institucional') {
                const colores = { activo:'success', suspendido:'danger', baja:'dark', egresado:'secondary' };
                val = `<span class="badge bg-${colores[val] || 'secondary'}">${val}</span>`;
            }
            // Badge para tipo_registro
            if (c.key === 'tipo_registro') {
                val = `<span class="badge ${val === 'entrada' ? 'bg-primary' : 'bg-secondary'}">${val}</span>`;
            }
            return `<td>${val}</td>`;
        }).join('');

        const id = fila[cfg.pk];
        return `<tr>
            ${celdas}
            <td>
                <button class="btn btn-sm btn-outline-primary me-1"
                        onclick="abrirModalEditar(${id})">
                    ✏️ Editar
                </button>
                <button class="btn btn-sm btn-outline-danger"
                        onclick="confirmarEliminar(${id})">
                    🗑️ Eliminar
                </button>
            </td>
        </tr>`;
    }).join('');
}

// ── Filtrado local ────────────────────────────────────────────────────────
function filtrarTabla() {
    const q = document.getElementById('inputBusqueda').value.toLowerCase().trim();
    if (!q) { renderizarTabla(datosTabla); return; }

    const filtrados = datosTabla.filter(fila =>
        Object.values(fila).some(v => String(v ?? '').toLowerCase().includes(q))
    );
    renderizarTabla(filtrados);
}

// ── Modal: Crear ──────────────────────────────────────────────────────────
function abrirModalCrear() {
    registroActual = null;
    document.getElementById('modalTitulo').textContent = `Nuevo Registro — ${CONFIG_TABLAS[tablaActual].label}`;
    construirFormulario(null);
    limpiarMensajeModal();
    new bootstrap.Modal(document.getElementById('modalCrud')).show();
}

// ── Modal: Editar ─────────────────────────────────────────────────────────
function abrirModalEditar(id) {
    const cfg  = CONFIG_TABLAS[tablaActual];
    const fila = datosTabla.find(r => String(r[cfg.pk]) === String(id));
    if (!fila) { alert('Registro no encontrado en la carga actual.'); return; }

    registroActual = fila;
    document.getElementById('modalTitulo').textContent = `Editar Registro — ${cfg.label} (ID: ${id})`;
    construirFormulario(fila);
    limpiarMensajeModal();
    new bootstrap.Modal(document.getElementById('modalCrud')).show();
}

// ── Construir formulario dinámico ─────────────────────────────────────────
function construirFormulario(fila) {
    const cfg  = CONFIG_TABLAS[tablaActual];
    const form = document.getElementById('modalFormulario');

    form.innerHTML = cfg.campos.map(campo => {
        const valor = fila ? (fila[campo.name] ?? '') : '';
        const req   = campo.required ? 'required' : '';
        const hint  = campo.hint ? `<div class="form-text">${campo.hint}</div>` : '';

        let input;
        if (campo.type === 'select') {
            const opciones = campo.opciones.map(op =>
                `<option value="${op}" ${op === valor ? 'selected' : ''}>${op}</option>`
            ).join('');
            input = `<select class="form-select" id="campo_${campo.name}" name="${campo.name}" ${req}>
                         <option value="">Seleccionar...</option>${opciones}
                     </select>`;
        } else {
            input = `<input type="${campo.type}"
                            class="form-control"
                            id="campo_${campo.name}"
                            name="${campo.name}"
                            value="${String(valor).replace(/"/g, '&quot;')}"
                            ${req}>`;
        }

        return `<div class="mb-3">
                    <label for="campo_${campo.name}" class="form-label fw-semibold">
                        ${campo.label}${campo.required ? ' <span class="text-danger">*</span>' : ''}
                    </label>
                    ${input}
                    ${hint}
                </div>`;
    }).join('');
}

// ── Guardar registro (crear o actualizar) ─────────────────────────────────
async function guardarRegistro() {
    const cfg    = CONFIG_TABLAS[tablaActual];
    const form   = document.getElementById('modalFormulario');
    const inputs = form.querySelectorAll('input, select, textarea');

    // Validar campos requeridos
    let valido = true;
    inputs.forEach(inp => {
        inp.classList.remove('is-invalid');
        if (inp.required && !inp.value.trim()) {
            inp.classList.add('is-invalid');
            valido = false;
        }
    });
    if (!valido) { mostrarMensajeModal('danger', 'Por favor completa los campos obligatorios.'); return; }

    // Recolectar datos
    const datos = {};
    inputs.forEach(inp => {
        if (inp.name && inp.value.trim() !== '') {
            // No enviar password vacío al editar
            if (inp.type === 'password' && registroActual && !inp.value.trim()) return;
            datos[inp.name] = inp.value.trim();
        }
    });

    const esModoEdicion = registroActual !== null;
    const payload = esModoEdicion
        ? { accion: 'actualizar', tabla: tablaActual, id: registroActual[cfg.pk], datos }
        : { accion: 'crear',      tabla: tablaActual, datos };

    document.getElementById('btnGuardarModal').disabled = true;
    document.getElementById('btnGuardarModal').textContent = 'Guardando...';

    try {
        const res = await enviarDatos('crud.php', payload);

        if (res && res.ok) {
            mostrarMensajeModal('success', res.mensaje || 'Operación exitosa.');
            await cargarTabla(tablaActual); // recargar tabla
            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('modalCrud'))?.hide();
            }, 900);
        } else {
            mostrarMensajeModal('danger', res?.mensaje || 'Error al guardar.');
        }
    } catch (e) {
        mostrarMensajeModal('danger', 'Error de conexión con el servidor.');
    } finally {
        document.getElementById('btnGuardarModal').disabled = false;
        document.getElementById('btnGuardarModal').textContent = 'Guardar';
    }
}

// ── Eliminar registro ─────────────────────────────────────────────────────
async function confirmarEliminar(id) {
    const cfg  = CONFIG_TABLAS[tablaActual];
    const fila = datosTabla.find(r => String(r[cfg.pk]) === String(id));
    const info = fila
        ? (fila.nombre ? `${fila.nombre} ${fila.apellido_paterno ?? ''}` : `ID ${id}`)
        : `ID ${id}`;

    if (!confirm(`¿Eliminar el registro "${info.trim()}" de la tabla "${cfg.label}"?\n\nEsta acción no se puede deshacer.`)) return;

    try {
        const res = await enviarDatos('crud.php', { accion: 'eliminar', tabla: tablaActual, id });

        if (res && res.ok) {
            // Eliminar fila del caché local y re-renderizar (más rápido que recargar)
            datosTabla = datosTabla.filter(r => String(r[cfg.pk]) !== String(id));
            renderizarTabla(datosTabla);
            document.getElementById('contadorRegistros').textContent = `${datosTabla.length} registros`;
            mostrarToast('success', 'Registro eliminado correctamente.');
        } else {
            alert('Error al eliminar: ' + (res?.mensaje || 'Error desconocido.'));
        }
    } catch (e) {
        alert('Error de conexión con el servidor.');
    }
}

// ── Utilidades de UI ──────────────────────────────────────────────────────
function mostrarCargando(estado) {
    document.getElementById('spinnerCarga').style.display = estado ? 'block' : 'none';
    document.getElementById('contenedorTabla').style.display = estado ? 'none' : 'block';
}

function mostrarError(msg) {
    document.getElementById('alertaError').textContent = msg;
    document.getElementById('alertaError').style.display = 'block';
    setTimeout(() => { document.getElementById('alertaError').style.display = 'none'; }, 5000);
}

function mostrarMensajeModal(tipo, msg) {
    const el = document.getElementById('mensajeModal');
    el.className = `alert alert-${tipo} py-2 mt-2`;
    el.textContent = msg;
    el.style.display = 'block';
}

function limpiarMensajeModal() {
    const el = document.getElementById('mensajeModal');
    el.style.display = 'none';
    el.textContent  = '';
}

function mostrarToast(tipo, msg) {
    const toast = document.getElementById('toastNotif');
    const body  = document.getElementById('toastBody');
    toast.className = `toast align-items-center text-white bg-${tipo} border-0`;
    body.textContent = msg;
    bootstrap.Toast.getOrCreateInstance(toast).show();
}
