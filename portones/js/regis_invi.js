document.addEventListener('DOMContentLoaded', () => {
    
    // Autocompletar la fecha de hoy por defecto para mayor comodidad
    const inputFecha = document.getElementById('fecha_visita');
    const hoy = new Date().toISOString().split('T')[0];
    inputFecha.value = hoy;

    const formulario = document.getElementById('formularioInvitado');

    formulario.addEventListener('submit', (e) => {
        e.preventDefault();

        // Recolección básica de datos
        const datosInvitado = {
            nombre: document.getElementById('nombre_invitado').value,
            apellido_paterno: document.getElementById('apellido_p_invitado').value,
            apellido_materno: document.getElementById('apellido_m_invitado').value,
            curp: document.getElementById('cedula').value,
            telefono: document.getElementById('telefono_invitado').value,
            motivo: document.getElementById('motivo').value,
            persona_a_visitar: document.getElementById('persona_visitar').value,
            fecha: document.getElementById('fecha_visita').value,
            hora: document.getElementById('hora_visita').value,
            duracion_horas: document.getElementById('duracion').value
        };

        /* ====================================================================
        ¡ATENCIÓN BACKEND (AWS)!
        ====================================================================
        Para guardar 'datosInvitado', la BD necesita ajustes.
        
        Sugerencia 1 (Más limpia): Crear una tabla 'invitado' independiente.
        Sugerencia 2 (Parche): Modificar ENUM tipo_persona en tabla 'persona'
        agregando 'invitado', y crear una tabla 'visita' que enlace el 
        id_persona con los campos: motivo, a_quien_visita, fecha, duracion.
        ====================================================================
        */

        console.log("Datos del invitado listos para enviar:", datosInvitado);
        alert("Simulación: Datos del invitado procesados.\n\nFalta conexión con la Base de Datos para generar el código QR temporal.");
        
        // Aquí iría el código real:
        // const respuesta = await enviarDatos('registrar_invitado', datosInvitado);
        // if(respuesta.ok) { mostrarQR(respuesta.qrCode); }
    });
});