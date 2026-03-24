async function enviarSolicitud() {

    const nombre   = document.getElementById("nombre").value;
    const equipo   = document.getElementById("equipo").value;
    const cantidad = parseInt(document.getElementById("cantidad").value);
    const aula     = document.getElementById("aula").value;
    const horas    = parseInt(document.getElementById("horas").value);
    const mensaje  = document.getElementById("mensaje");

    mensaje.style.color = "red";

    // Verificar sesión
    const sesion = JSON.parse(sessionStorage.getItem("sesionActiva"));

    if (!sesion) {
        window.location.href = "index.html";
        return;
    }

    if (!nombre || !aula || !horas) {
        mensaje.innerText = "Faltan datos obligatorios.";
        return;
    }

    if (horas > 4) {
        mensaje.innerText = "El período máximo permitido es de 4 horas.";
        return;
    }

    try {
        const response = await fetch("api/solicitudes.php?action=enviar", {
            method:  "POST",
            headers: { "Content-Type": "application/json" },
            body:    JSON.stringify({
                nombre,
                tipoUsuario: sesion.tipo,
                equipo,
                cantidad,
                aula,
                horas
            })
        });

        const data = await response.json();

        if (data.error) {
            mensaje.innerText = "Error: " + data.error;
            return;
        }

        mensaje.style.color = "green";
        mensaje.innerText   = "✅ Solicitud enviada correctamente. Pendiente de aprobación.";

        document.getElementById("formSolicitud").reset();

    } catch (err) {
        mensaje.innerText = "Error de conexión con el servidor: " + err.message;
    }
}
