document.addEventListener('DOMContentLoaded', () => {
    
    const selectTipoPersona = document.getElementById('tipo_persona');
    const selectNivelEducativo = document.getElementById('nivel_educativo');
    
    // Contenedores dinámicos
    const divAlumno = document.getElementById('campos_alumno');
    const divDocente = document.getElementById('campos_docente');
    const divAdministrativo = document.getElementById('campos_administrativo');
    const divCarrera = document.getElementById('campo_carrera');

    // Escuchar cambios en el Tipo de Usuario
    selectTipoPersona.addEventListener('change', (e) => {
        const tipo = e.target.value;
        
        // Ocultar todos primero
        divAlumno.classList.add('d-none');
        divDocente.classList.add('d-none');
        divAdministrativo.classList.add('d-none');

        // Mostrar según selección
        if (tipo === 'alumno') {
            divAlumno.classList.remove('d-none');
        } else if (tipo === 'docente') {
            divDocente.classList.remove('d-none');
        } else if (tipo === 'administrativo') {
            divAdministrativo.classList.remove('d-none');
        }
    });

    // Escuchar cambios en el Nivel Educativo (solo para alumnos)
    selectNivelEducativo.addEventListener('change', (e) => {
        const nivel = e.target.value;
        
        // Si el valor es 2 (Universidad), mostramos el campo de carrera
        if (nivel === '2') {
            divCarrera.classList.remove('d-none');
        } else {
            divCarrera.classList.add('d-none');
        }
    });

    // Interceptar el envío del formulario
    document.getElementById('formularioRegistro').addEventListener('submit', (e) => {
        e.preventDefault(); // Evita que la página recargue
        
        // Aquí recolectaremos los datos para enviarlos con api.js
        console.log("Formulario listo para enviar datos a AWS.");
        alert("Simulación: Datos recolectados. Pendiente conexión con backend.");
    });
});