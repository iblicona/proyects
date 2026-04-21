document.addEventListener('DOMContentLoaded', () => {

    const msgError  = document.getElementById('msg_error');
    const msgInfo   = document.getElementById('msg_info');
    const zonaImp   = document.getElementById('zona-impresion');
    let   qrInstance = null;

    // ── Cargar desde sessionStorage si viene de registro ────────────────────
    const datos = sessionStorage.getItem('credencial_data');
    if (datos) {
        try {
            const d = JSON.parse(datos);
            renderCredencial({
                nombre:    d.nombre,
                tipo:      d.tipo,
                qr_token:  d.qr_token,
                id_persona: d.id_persona
            });
            sessionStorage.removeItem('credencial_data');
        } catch {}
    } else {
        msgInfo.classList.remove('d-none');
    }

    // ── Búsqueda manual ──────────────────────────────────────────────────────
    document.getElementById('btn_buscar').addEventListener('click', async () => {
        const val = document.getElementById('buscar_id').value.trim();
        if (!val) return;

        let id_persona = null;
        if (/^ITLA-(\d+)$/i.test(val)) {
            id_persona = parseInt(val.replace(/^ITLA-/i, ''));
        } else if (/^\d+$/.test(val)) {
            id_persona = parseInt(val);
        } else {
            mostrarError('Ingresa un ID numérico o un token ITLA-XXXXXX.');
            return;
        }

        try {
            // Buscar persona en la BD a través de crud.php listar (filtramos por id client-side)
            const res = await enviarDatos('crud.php', { accion: 'listar', tabla: 'persona' });
            if (!res || !res.ok) { mostrarError('No se pudo consultar la base de datos.'); return; }

            const persona = res.datos.find(p => parseInt(p.id_persona) === id_persona);
            if (!persona) { mostrarError(`Persona con ID ${id_persona} no encontrada.`); return; }

            // Buscar datos adicionales (matrícula si es alumno)
            let extra = '';
            let mat   = 'ID: ' + id_persona;

            if (persona.tipo_persona === 'alumno') {
                const resA = await enviarDatos('crud.php', { accion: 'listar', tabla: 'alumno' });
                if (resA && resA.ok) {
                    const al = resA.datos.find(a => parseInt(a.id_persona) === id_persona);
                    if (al) {
                        mat   = al.matricula ?? mat;
                        extra = `Semestre: ${al.grado ?? '-'} | Grupo: ${al.grupo ?? '-'}`;
                    }
                }
            } else if (persona.tipo_persona === 'docente') {
                const resD = await enviarDatos('crud.php', { accion: 'listar', tabla: 'docente' });
                if (resD && resD.ok) {
                    const doc = resD.datos.find(d => parseInt(d.id_persona) === id_persona);
                    if (doc) extra = `Especialidad: ${doc.especialidad ?? '-'}`;
                }
            } else if (persona.tipo_persona === 'administrativo') {
                const resAdm = await enviarDatos('crud.php', { accion: 'listar', tabla: 'administrativo' });
                if (resAdm && resAdm.ok) {
                    const adm = resAdm.datos.find(a => parseInt(a.id_persona) === id_persona);
                    if (adm) extra = `Puesto: ${adm.puesto ?? '-'}`;
                }
            }

            renderCredencial({
                nombre:     persona.nombre + ' ' + persona.apellido_paterno + ' ' + (persona.apellido_materno ?? ''),
                tipo:       persona.tipo_persona,
                qr_token:   'ITLA-' + String(id_persona).padStart(6, '0'),
                id_persona: id_persona,
                matricula:  mat,
                extra:      extra
            });

        } catch (err) {
            mostrarError('Error de conexión con el servidor.');
        }
    });

    // ── Renderizar credencial ────────────────────────────────────────────────
    function renderCredencial(data) {
        msgInfo.classList.add('d-none');
        msgError.classList.add('d-none');

        document.getElementById('c_nombre').textContent  = data.nombre    ?? '-';
        document.getElementById('c_tipo').textContent    = capitalizar(data.tipo ?? '');
        document.getElementById('c_extra').textContent   = data.extra     ?? '';
        document.getElementById('c_matricula').textContent = data.matricula ?? ('ID: ' + data.id_persona);
        document.getElementById('c_token_label').textContent = data.qr_token ?? '';

        // Generar QR
        const contenedor = document.getElementById('c_qr');
        contenedor.innerHTML = '';
        qrInstance = null;
        if (typeof QRCode !== 'undefined') {
            qrInstance = new QRCode(contenedor, {
                text: data.qr_token ?? String(data.id_persona),
                width: 80, height: 80,
                correctLevel: QRCode.CorrectLevel.H
            });
        }

        zonaImp.classList.remove('d-none');
    }

    // ── Descargar PNG ────────────────────────────────────────────────────────
    document.getElementById('btn_descargar')?.addEventListener('click', async () => {
        const card = document.getElementById('credencial-preview');
        try {
            const canvas = await html2canvas(card, { scale: 2, useCORS: true });
            const link   = document.createElement('a');
            link.download = 'credencial_itla.png';
            link.href     = canvas.toDataURL('image/png');
            link.click();
        } catch { alert('No se pudo generar la imagen. Usa la opción de imprimir.'); }
    });

    // ── Copiar token QR ──────────────────────────────────────────────────────
    document.getElementById('btn_copiar_qr')?.addEventListener('click', () => {
        const token = document.getElementById('c_token_label').textContent;
        if (token) {
            navigator.clipboard.writeText(token)
                .then(() => alert('Token copiado: ' + token))
                .catch(() => alert('Token: ' + token));
        }
    });

    function mostrarError(msg) {
        msgError.textContent = msg;
        msgError.classList.remove('d-none');
        zonaImp.classList.add('d-none');
    }
    function capitalizar(str) {
        return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
    }
});
