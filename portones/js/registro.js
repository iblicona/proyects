document.addEventListener('DOMContentLoaded', () => {

    const selectTipoPersona   = document.getElementById('tipo_persona');
    const selectNivelEducativo = document.getElementById('nivel_educativo');

    // Contenedores dinámicos
    const divAlumno        = document.getElementById('campos_alumno');
    const divDocente       = document.getElementById('campos_docente');
    const divAdministrativo= document.getElementById('campos_administrativo');
    const divCarrera       = document.getElementById('campo_carrera');

    // Escuchar cambios en el Tipo de Usuario
    selectTipoPersona.addEventListener('change', (e) => {
        const tipo = e.target.value;

        divAlumno.classList.add('d-none');
        divDocente.classList.add('d-none');
        divAdministrativo.classList.add('d-none');

        if (tipo === 'alumno')          divAlumno.classList.remove('d-none');
        else if (tipo === 'docente')    divDocente.classList.remove('d-none');
        else if (tipo === 'administrativo') divAdministrativo.classList.remove('d-none');
    });

    // Mostrar campo carrera sólo para Universidad (nivel 2)
    selectNivelEducativo.addEventListener('change', (e) => {
        if (e.target.value === '2') divCarrera.classList.remove('d-none');
        else                        divCarrera.classList.add('d-none');
    });

    // Envío del formulario
    document.getElementById('formularioRegistro').addEventListener('submit', async (e) => {
        e.preventDefault();

        const tipo = selectTipoPersona.value;

        // Datos comunes (tabla persona)
        const datos = {
            nombre:           document.getElementById('nombre').value.trim(),
            apellido_paterno: document.getElementById('apellido_paterno').value.trim(),
            apellido_materno: document.getElementById('apellido_materno').value.trim(),
            fecha_nacimiento: document.getElementById('fecha_nacimiento').value || null,
            telefono:         document.getElementById('telefono').value.trim()  || null,
            correo:           document.getElementById('correo').value.trim()    || null,
            tipo_persona:     tipo
        };

        // Datos específicos según tipo
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
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Guardando...';

        try {
            const respuesta = await enviarDatos('registro.php', datos);

            if (respuesta && respuesta.ok) {
                alert(`✅ Registro guardado. ID persona: ${respuesta.id_persona}`);
                e.target.reset();
                divAlumno.classList.add('d-none');
                divDocente.classList.add('d-none');
                divAdministrativo.classList.add('d-none');
            } else {
                alert('Error: ' + (respuesta ? respuesta.mensaje : 'Sin respuesta del servidor.'));
            }
        } catch (err) {
            alert('No se pudo conectar con el servidor.');
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Guardar Registro';
        }
    });
});