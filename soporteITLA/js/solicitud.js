let solicitudes = JSON.parse(localStorage.getItem("solicitudes")) || [];

function enviarSolicitud() {

    const nombre = document.getElementById("nombre").value;
    const tipoUsuario = document.getElementById("tipoUsuario").value;
    const equipo = document.getElementById("equipo").value;
    const cantidad = parseInt(document.getElementById("cantidad").value);
    const aula = document.getElementById("aula").value;
    const horas = parseInt(document.getElementById("horas").value);

    const mensaje = document.getElementById("mensaje");
    mensaje.style.color = "red";

    if (!nombre || !aula || !horas) {
        mensaje.innerText = "Faltan datos obligatorios.";
        return;
    }

    if (horas > 4) {
        mensaje.innerText = "El periodo máximo permitido es de 4 horas.";
        return;
    }

    solicitudes.push({
        id: Date.now(),
        nombre,
        tipoUsuario,
        equipo,
        cantidad,
        aula,
        horas,
        estado: "Pendiente"
    });

    localStorage.setItem("solicitudes", JSON.stringify(solicitudes));

    mensaje.style.color = "green";
    mensaje.innerText = "Solicitud enviada correctamente. Pendiente de aprobación.";

    document.getElementById("formSolicitud").reset();
}