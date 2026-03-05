document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('formLogin');

    if (formLogin) {
        formLogin.addEventListener('submit', (e) => {
            e.preventDefault(); // Evita que la página recargue

            const user = document.getElementById('username').value;
            const pass = document.getElementById('password').value;

            /* ====================================================================
            ¡ATENCIÓN BACKEND (AWS)!
            Aquí se debe hacer un fetch (POST) al endpoint de autenticación.
            Validar contra la tabla 'usuario' (username, password, rol).
            El backend debe devolver un Token JWT y el 'rol' del usuario.
            ==================================================================== */

            console.log(`Intentando acceder con usuario: ${user}`);
            
            // Simulación: Aceptamos cualquier usuario y le asignamos rol 'admin'
            // En un futuro, esto se guarda desde la respuesta real de la base de datos
            localStorage.setItem('sesion_activa', 'true');
            localStorage.setItem('usuario_nombre', user);
            localStorage.setItem('usuario_rol', 'admin'); // Valores posibles de tu BD: admin, control, consulta

            // Redirigimos al menú
            window.location.href = 'paginas/menu.html';
        });
    }
});