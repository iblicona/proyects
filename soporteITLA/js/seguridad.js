/* ================================
   OBTENER SESIÓN
================================ */

const sesion = JSON.parse(sessionStorage.getItem("sesionActiva"));

if (!sesion) {
    window.location.href = "index.html";
}

/* ================================
   CONTROL DE ACCESO POR ROL
================================ */

const pagina = window.location.pathname;

if (pagina.includes("rentas.html") && sesion && sesion.tipo !== "soporte") {
    alert("No tienes permisos para acceder a esta página.");
    window.location.href = "solicitud.html";
}

/* ================================
   MOSTRAR USUARIO ACTIVO
================================ */

document.addEventListener("DOMContentLoaded", () => {
    const usuarioSpan = document.getElementById("usuarioActivo");

    if (usuarioSpan && sesion) {
        usuarioSpan.innerText = sesion.usuario + " (" + sesion.tipo + ")";
    }
});

/* ================================
   CERRAR SESIÓN
================================ */

function cerrarSesion() {
    sessionStorage.removeItem("sesionActiva");
    window.location.href = "index.html";
}
