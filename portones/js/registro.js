document.addEventListener('DOMContentLoaded', () => {

    const selectTipoPersona    = document.getElementById('tipo_persona');
    const selectNivelEducativo = document.getElementById('nivel_educativo');
    const divAlumno            = document.getElementById('campos_alumno');
    const divDocente           = document.getElementById('campos_docente');
    const divAdministrativo    = document.getElementById('campos_administrativo');
    const divCarrera           = document.getElementById('campo_carrera');
    const selectCarrera        = document.getElementById('carrera');

    // ── Cargar carreras desde la BD ──────────────────────────────────────────
    async function cargarCarreras() {
        try {
            const res = await enviarDatos('crud.php', { accion: 'listar', tabla: 'carrera' });
            if (res && res.ok && res.datos.length > 0) {
                selectCarrera.innerHTML = '<option value="">Seleccionar...</option>';
                res.datos.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value       = c.id_carrera;
                    opt.textContent = c.nombre_carrera ?? c.nombre ?? `Carrera ${c.id_carrera}`;
                    selectCarrera.appendChild(opt);
                });
            }
        } catch { /* si falla, se queda con las opciones estáticas */ }
    }
    cargarCarreras();

    // ── Cambio de tipo de persona ─────────────────────────────────────────────
    selectTipoPersona.addEventListener('change', (e) => {
        const tipo = e.target.value;
        divAlumno.classList.add('d-none');
        divDocente.classList.add('d-none');
        divAdministrativo.classList.add('d-none');
        if (tipo === 'alumno')          divAlumno.classList.remove('d-none');
        else if (tipo === 'docente')    divDocente.classList.remove('d-none');
        else if (tipo === 'administrativo') divAdministrativo.classList.remove('d-none');
    });

    // ── Mostrar carrera sólo para Universidad ──────────────────────────────────
    selectNivelEducativo.addEventListener('change', (e) => {
        if (e.target.value === '2') divCarrera.classList.remove('d-none');
        else                        divCarrera.classList.add('d-none');
    });

    // ── Envío del formulario ─────────────────────────────────────────────────
    document.getElementById('formularioRegistro').addEventListener('submit', async (e) => {
        e.preventDefault();

        const tipo = selectTipoPersona.value;
        const datos = {
            nombre:           document.getElementById('nombre').value.trim(),
            apellido_paterno: document.getElementById('apellido_paterno').value.trim(),
            apellido_materno: document.getElementById('apellido_materno').value.trim(),
            fecha_nacimiento: document.getElementById('fecha_nacimiento').value || null,
            telefono:         document.getElementById('telefono').value.trim()  || null,
            correo:           document.getElementById('correo').value.trim()    || null,
            tipo_persona:     tipo
        };

        if (tipo === 'alumno') {
            datos.matricula            = document.getElementById('matricula').value.trim();
            datos.nivel_educativo      = document.getElementById('nivel_educativo').value;
            datos.carrera              = document.getElementById('carrera').value || null;
            datos.grado                = document.getElementById('grado').value   || null;
            datos.grupo                = document.getElementById('grupo').value.trim() || null;
            datos.cuatrimestre         = document.getElementById('cuatrimestre').value || null;
            datos.estado_institucional = document.getElementById('estado_institucional').value || 'activo';
        } else if (tipo === 'docente') {
            datos.especialidad  = document.getElementById('especialidad').value.trim();
            datos.nivel_docente = document.getElementById('nivel_docente').value;
        } else if (tipo === 'administrativo') {
            datos.puesto       = document.getElementById('puesto').value.trim();
            datos.departamento = document.getElementById('departamento').value.trim();
        }

        const btnSubmit = e.target.querySelector('[type="submit"]');
        btnSubmit.disabled    = true;
        btnSubmit.textContent = 'Guardando...';

        try {
            const respuesta = await enviarDatos('registro.php', datos);

            if (respuesta && respuesta.ok) {
                mostrarSeccionQR(respuesta);
            } else {
                alert('Error: ' + (respuesta ? respuesta.mensaje : 'Sin respuesta del servidor.'));
            }
        } catch (err) {
            alert('No se pudo conectar con el servidor.');
        } finally {
            btnSubmit.disabled    = false;
            btnSubmit.textContent = 'Guardar Registro';
        }
    });

    // ── Mostrar QR tras registro exitoso ─────────────────────────────────────
    function mostrarSeccionQR(respuesta) {
        // Ocultar el formulario
        document.getElementById('formularioRegistro').classList.add('d-none');

        // Rellenar datos en la sección de éxito
        document.getElementById('qr_nombre').textContent  = respuesta.nombre  ?? '-';
        document.getElementById('qr_tipo').textContent    = respuesta.tipo    ?? '-';
        document.getElementById('qr_token').textContent   = respuesta.qr_token ?? '-';
        document.getElementById('qr_id').textContent      = respuesta.id_persona ?? '-';

        // Generar QR con la librería QRCode.js
        const contenedor = document.getElementById('qr_imagen');
        contenedor.innerHTML = '';
        if (typeof QRCode !== 'undefined') {
            new QRCode(contenedor, {
                text:         respuesta.qr_token,
                width:        180,
                height:       180,
                correctLevel: QRCode.CorrectLevel.H
            });
        } else {
            contenedor.innerHTML = `<p class="text-muted small">QR: <strong>${respuesta.qr_token}</strong></p>`;
        }

        // Guardar datos para credencial en sessionStorage
        sessionStorage.setItem('credencial_data', JSON.stringify(respuesta));

        document.getElementById('seccion_qr').classList.remove('d-none');
    }

    // ── Nuevo registro ───────────────────────────────────────────────────────
    document.getElementById('btn_nuevo_registro')?.addEventListener('click', () => {
        document.getElementById('formularioRegistro').classList.remove('d-none');
        document.getElementById('seccion_qr').classList.add('d-none');
        document.getElementById('formularioRegistro').reset();
        divAlumno.classList.add('d-none');
        divDocente.classList.add('d-none');
        divAdministrativo.classList.add('d-none');
    });

    // ── Ir a credencial ──────────────────────────────────────────────────────
    document.getElementById('btn_credencial')?.addEventListener('click', () => {
        window.location.href = 'credencial.html';
    });
});
