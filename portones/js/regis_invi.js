document.addEventListener('DOMContentLoaded', () => {

    // Autocompletar la fecha de hoy por omisión
    const inputFecha = document.getElementById('fecha_visita');
    inputFecha.value = new Date().toISOString().split('T')[0];

    const formulario = document.getElementById('formularioInvitado');

    formulario.addEventListener('submit', async (e) => {
        e.preventDefault();

        const datosInvitado = {
            nombre:            document.getElementById('nombre_invitado').value.trim(),
            apellido_paterno:  document.getElementById('apellido_p_invitado').value.trim(),
            apellido_materno:  document.getElementById('apellido_m_invitado').value.trim(),
            curp:              document.getElementById('cedula').value.trim(),
            telefono:          document.getElementById('telefono_invitado').value.trim() || null,
            motivo:            document.getElementById('motivo').value.trim(),
            persona_a_visitar: document.getElementById('persona_visitar').value.trim(),
            fecha:             document.getElementById('fecha_visita').value,
            hora:              document.getElementById('hora_visita').value,
            duracion_horas:    parseInt(document.getElementById('duracion').value, 10) || 1
        };

        const btnSubmit = formulario.querySelector('[type="submit"]');
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Registrando...';

        try {
            const respuesta = await enviarDatos('invitado.php', datosInvitado);

            if (respuesta && respuesta.ok) {
                alert(`✅ Invitado registrado correctamente.\nID Visita: ${respuesta.id_visita}`);
                formulario.reset();
                // Restaurar fecha de hoy tras reset
                inputFecha.value = new Date().toISOString().split('T')[0];
            } else {
                alert('Error: ' + (respuesta ? respuesta.mensaje : 'Sin respuesta del servidor.'));
            }
        } catch (err) {
            alert('No se pudo conectar con el servidor.');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Generar QR de Invitado';
        }
    });
});