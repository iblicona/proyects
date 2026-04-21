document.addEventListener('DOMContentLoaded', () => {

    const tablaBody      = document.getElementById('tabla_monitoreo');
    const inputMatricula = document.getElementById('buscar_matricula');
    const formBusqueda   = document.getElementById('formBusqueda');
    const badgeServidor  = document.getElementById('badge_servidor');
    let   intervalo      = null;

    // ── Cargar datos reales desde monitoreo.php ───────────────────────────────
    async function cargarDatos() {
        try {
            const res = await fetch(`${API_URL}/monitoreo.php`);
            const data = await res.json();

            if (!data.ok) return;

            // Actualizar badge servidor
            if (badgeServidor) {
                badgeServidor.textContent = 'Servidor BD: Conectado';
                badgeServidor.className   = 'badge bg-success me-3';
            }

            // Filtrar si hay búsqueda activa
            const filtro = inputMatricula.value.trim().toLowerCase();
            const registros = filtro
                ? data.registros.filter(r =>
                    (r.matricula ?? '').toLowerCase().includes(filtro) ||
                    (r.nombre    ?? '').toLowerCase().includes(filtro))
                : data.registros;

            renderTabla(registros);
            renderStats(data.stats);

        } catch (err) {
            console.error('Error cargando monitoreo:', err);
            if (badgeServidor) {
                badgeServidor.textContent = 'Servidor BD: Error';
                badgeServidor.className   = 'badge bg-danger me-3';
            }
        }
    }

    // ── Renderizar tabla ──────────────────────────────────────────────────────
    function renderTabla(registros) {
        if (!registros.length) {
            tablaBody.innerHTML = `
                <tr><td colspan="7" class="text-center text-muted py-4">
                    Sin registros de acceso hoy.
                </td></tr>`;
            return;
        }

        tablaBody.innerHTML = registros.map(r => {
            const estados = {
                activo:    'bg-success',
                suspendido:'bg-danger',
                baja:      'bg-dark',
                egresado:  'bg-secondary'
            };
            const badgeEstado = `<span class="badge ${estados[r.estado_institucional] ?? 'bg-secondary'}">${r.estado_institucional ?? 'N/A'}</span>`;

            const perfil = r.tipo_persona === 'alumno'
                ? (r.id_nivel == 1 ? 'Alumno (Preparatoria)' : 'Alumno (Universidad)')
                : (r.tipo_persona === 'docente' ? 'Docente' : 'Administrativo');

            let badgeAcceso, clsRow = '';
            const sinSalida = !r.hora_salida && r.tipo_registro === 'entrada';
            if (r.estado_institucional !== 'activo') {
                badgeAcceso = `<span class="badge bg-danger">Acceso Bloqueado</span>`;
                clsRow = 'class="table-danger"';
            } else if (sinSalida) {
                badgeAcceso = `<span class="badge bg-primary">Adentro</span>`;
            } else {
                badgeAcceso = `<span class="badge bg-secondary">Salió</span>`;
            }

            const horaSalida = r.hora_salida
                ? `<span>${r.hora_salida}</span>`
                : (r.tipo_registro === 'salida' && !r.hora_entrada
                    ? `<span class="text-danger fw-bold">${r.hora_salida ?? '-'} (Intento)</span>`
                    : '-');

            return `<tr ${clsRow}>
                <td>${r.nombre ?? '-'}</td>
                <td>${perfil}</td>
                <td>${r.matricula ?? '-'}</td>
                <td>${badgeEstado}</td>
                <td>${r.hora_entrada ?? '-'}</td>
                <td>${horaSalida}</td>
                <td>${badgeAcceso}</td>
            </tr>`;
        }).join('');
    }

    // ── Renderizar estadísticas ───────────────────────────────────────────────
    function renderStats(stats) {
        const ids = { adentro: 'stat_adentro', entradas: 'stat_entradas', denegados: 'stat_denegados', salidas: 'stat_salidas' };
        for (const [key, id] of Object.entries(ids)) {
            const el = document.getElementById(id);
            if (el) el.textContent = stats[key] ?? 0;
        }
    }

    // ── Búsqueda (filtra los datos ya cargados) ───────────────────────────────
    formBusqueda.addEventListener('submit', (e) => {
        e.preventDefault();
        cargarDatos();
    });

    // ── Auto-refresh cada 10 segundos ─────────────────────────────────────────
    cargarDatos();
    intervalo = setInterval(cargarDatos, 10000);

    // Limpiar intervalo al salir de la página
    window.addEventListener('beforeunload', () => clearInterval(intervalo));
});
