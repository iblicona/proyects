// URL base del backend PHP en el servidor
const API_URL = 'http://34.226.236.94/portones/php';

// Función genérica para enviar datos (POST) a la base de datos
async function enviarDatos(endpoint, datos) {
    try {
        const respuesta = await fetch(`${API_URL}/${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datos)
        });
        return await respuesta.json();
    } catch (error) {
        console.error("Error de conexión con el servidor:", error);
        alert("No se pudo conectar con la base de datos.");
    }
}