document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Verificación de sesión
    const sesionActiva = localStorage.getItem('sesion_activa');
    
    if (!sesionActiva) {
        // Si alguien intenta entrar a menu.html sin haber pasado por el login (index.html)
        alert("Acceso denegado. Por favor, inicie sesión primero.");
        window.location.href = '../index.html';
        return;
    }

    // 2. Cargar datos del usuario en la interfaz
    const nombreUsuario = localStorage.getItem('usuario_nombre') || 'Usuario';
    const rolUsuario = localStorage.getItem('usuario_rol') || 'desconocido';
    
    document.getElementById('infoUsuario').innerHTML = `Bienvenido, <strong>${nombreUsuario}</strong> <span class="badge bg-teal ms-1">${rolUsuario}</span>`;

    // 3. Control de acceso basado en roles (Simulación)
    const btnAdmin = document.getElementById('btnAdmin');
    if (rolUsuario !== 'admin') {
        // Si el usuario en BD tiene rol 'consulta' o 'control', no debería entrar a admin
        // Aquí lo dejamos visualmente bloqueado para demostrar la funcionalidad
        btnAdmin.classList.add('disabled');
        btnAdmin.onclick = (e) => {
            e.preventDefault();
            alert("No tienes permisos de Administrador para acceder a este módulo.");
        };
    }

    // 4. Cerrar Sesión
    const btnSalir = document.getElementById('btnSalir');
    if (btnSalir) {
        btnSalir.addEventListener('click', () => {
            // Limpiamos los datos del navegador
            localStorage.clear();
            // Redirigimos al index
            window.location.href = '../index.html';
        });
    }
});