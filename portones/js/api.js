// URL base que te dará la persona encargada del Backend (ej. http://localhost:5000/api)
const API_URL = 'AQUI_VA_LA_URL_DEL_BACKEND'; 

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