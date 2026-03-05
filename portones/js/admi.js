document.addEventListener('DOMContentLoaded', () => {
    
    // Funcionalidad para el botón del Reporte Diario
    const btnReporteDiario = document.getElementById('btnReporteDiario');
    if (btnReporteDiario) {
        btnReporteDiario.addEventListener('click', () => {
            const hoy = new Date().toLocaleDateString();
            alert(`Simulación: Ejecutando consulta SQL en AWS:\n\nSELECT * FROM asistencia WHERE fecha = '${hoy}' AND tipo_registro = 'entrada';\n\nGenerando reporte en pantalla...`);
        });
    }

    // Funcionalidad para la ventana Modal de Cambio de Estado
    const btnGuardarEstado = document.getElementById('btnGuardarEstado');
    if (btnGuardarEstado) {
        btnGuardarEstado.addEventListener('click', () => {
            const matricula = document.getElementById('matBuscada').value;
            const nuevoEstado = document.getElementById('nuevoEstado').value;

            if (!matricula || !nuevoEstado) {
                alert("Por favor, ingresa la matrícula y selecciona un estado.");
                return;
            }

            // Aquí se enviaría la petición al servidor (api.js)
            const payload = {
                matricula: matricula,
                estado: nuevoEstado
            };
            console.log("Datos a enviar para actualizar estado:", payload);

            // Simulación de éxito
            alert(`¡Éxito! El estado del alumno con matrícula ${matricula} ha sido actualizado a "${nuevoEstado.toUpperCase()}" en la base de datos.`);
            
            // Cerrar el modal (usando la API de Bootstrap)
            const modalElement = document.getElementById('modalEstado');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
            
            // Limpiar formulario
            document.getElementById('formCambioEstado').reset();
        });
    }
});