document.addEventListener('DOMContentLoaded', () => {
    
    const formBusqueda = document.getElementById('formBusqueda');
    const inputMatricula = document.getElementById('buscar_matricula');

    formBusqueda.addEventListener('submit', async (e) => {
        e.preventDefault(); // Evita que la página se recargue al dar clic en buscar

        const matricula = inputMatricula.value.trim();

        if (matricula === "") {
            alert("Por favor, ingresa una matrícula o ID válida.");
            return;
        }

        // --- SIMULACIÓN DE LLAMADA A AWS ---
        console.log(`Buscando historial de accesos para la matrícula: ${matricula}`);
        
        // Aquí es donde en el futuro usarás api.js para hacer algo como:
        // const resultados = await enviarDatos('obtener_accesos', { matricula: matricula });
        
        alert(`Simulación: Buscando en la base de datos la matrícula ${matricula}. \n\nNota para el Backend: Esta función deberá cruzar las tablas 'asistencia', 'persona' y 'alumno' para validar el Estado Institucional y el Horario.`);
        
        // Limpiamos el input después de buscar
        inputMatricula.value = "";
    });

    // Código futuro para conectar con sockets y actualizar la tabla en tiempo real
    // cuando alguien escanee un QR en la entrada.
    function actualizarTablaEnTiempoReal(nuevoRegistro) {
        console.log("Se detectó un nuevo escaneo QR. Actualizando tabla...");
        // Lógica para inyectar una nueva fila <tr> en el tbody 'tabla_monitoreo'
    }
});