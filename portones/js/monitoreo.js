document.addEventListener('DOMContentLoaded', () => {

    const formBusqueda   = document.getElementById('formBusqueda');
    const inputMatricula = document.getElementById('buscar_matricula');
    const tablaBody      = document.getElementById('tabla_monitoreo');

    formBusqueda.addEventListener('submit', async (e) => {
        e.preventDefault();

        const matricula = inputMatricula.value.trim();
        if (!matricula) {
            alert('Por favor, ingresa una matrícula o ID válida.');
            return;
        }

        // Determinar tipo de evento: si ya hay una fila "Adentro" para esa matrícula → es salida
        const filaExistente = tablaBody.querySelector(`[data-matricula="${matricula}"]`);
        const tipo_evento = (filaExistente && filaExistente.dataset.estado === 'adentro')
            ? 'salida'
            : 'entrada';

        try {
            const res = await enviarDatos('acceso.php', { matricula, tipo_evento });
            inputMatricula.value = '';

            if (!res) {
                alert('Sin respuesta del servidor.');
                return;
            }

            // Renderizar resultado en la tabla
            renderizarFila(res, tipo_evento);

        } catch (err) {
            alert('No se pudo conectar con el servidor.');
        }
    });

    /**
     * Inserta o actualiza una fila en #tabla_monitoreo con el resultado del acceso.
     * @param {Object} res      - Respuesta de acceso.php
     * @param {string} tipo_evento - 'entrada' | 'salida'
     */
    function renderizarFila(res, tipo_evento) {
        const ahora = new Date().toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });

        // Eliminar fila previa de la misma matrícula si existe
        const filaAnterior = tablaBody.querySelector(`[data-matricula="${res.matricula}"]`);
        if (filaAnterior) filaAnterior.remove();

        // Badge de estado institucional
        let badgeEstado = '';
        const estados = {
            activo:    'bg-success',
            suspendido:'bg-danger',
            baja:      'bg-dark',
            egresado:  'bg-secondary'
        };
        const claseEstado = estados[res.estado_institucional] || 'bg-secondary';
        badgeEstado = `<span class="badge ${claseEstado}">${res.estado_institucional ?? 'N/A'}</span>`;

        // Badge de estado de acceso
        let badgeAcceso = '';
        let clsRow      = '';
        if (!res.permitido) {
            badgeAcceso = `<span class="badge bg-danger">Denegado</span>`;
            clsRow      = 'table-danger';
        } else if (tipo_evento === 'salida') {
            badgeAcceso = `<span class="badge bg-secondary">Salió</span>`;
        } else {
            badgeAcceso = `<span class="badge bg-primary">Adentro</span>`;
        }

        const estadoFila = (res.permitido && tipo_evento === 'entrada') ? 'adentro' : 'afuera';

        const horaEntrada = (tipo_evento === 'entrada' && res.permitido) ? ahora : '-';
        const horaSalida  = (tipo_evento === 'salida')
            ? (res.permitido ? ahora : `<span class="text-danger fw-bold">${ahora} (Intento)</span>`)
            : '-';

        const tr = document.createElement('tr');
        if (clsRow) tr.className = clsRow;
        tr.dataset.matricula = res.matricula;
        tr.dataset.estado    = estadoFila;

        tr.innerHTML = `
            <td>${res.nombre ?? '-'}</td>
            <td>${res.id_nivel == 1 ? 'Alumno (Preparatoria)' : 'Alumno (Universidad)'}</td>
            <td>${res.matricula}</td>
            <td>${badgeEstado}</td>
            <td>${horaEntrada}</td>
            <td>${horaSalida}</td>
            <td>${badgeAcceso}</td>
        `;

        // Insertar al inicio de la tabla para mostrar el más reciente primero
        tablaBody.insertBefore(tr, tablaBody.firstChild);

        if (!res.permitido) {
            alert(`⛔ ${res.mensaje}`);
        }
    }
});