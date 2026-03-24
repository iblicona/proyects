document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('formLogin');

    if (formLogin) {
        formLogin.addEventListener('submit', async (e) => {
            e.preventDefault();

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();

            if (!username || !password) {
                alert('Por favor ingresa usuario y contraseña.');
                return;
            }

            // Deshabilitar botón mientras se procesa
            const btnSubmit = formLogin.querySelector('[type="submit"]');
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Verificando...';

            try {
                // Llamada real al backend PHP
                const respuesta = await enviarDatos('login.php', { username, password });

                if (respuesta && respuesta.ok) {
                    // Guardar sesión en localStorage
                    localStorage.setItem('sesion_activa', 'true');
                    localStorage.setItem('usuario_nombre', respuesta.nombre);
                    localStorage.setItem('usuario_rol', respuesta.rol);

                    // Redirigir al menú
                    window.location.href = 'paginas/menu.html';
                } else {
                    const msg = respuesta ? respuesta.mensaje : 'Error desconocido.';
                    alert('Acceso denegado: ' + msg);
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Iniciar Sesión';
                }
            } catch (err) {
                alert('No se pudo conectar con el servidor. Intenta más tarde.');
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Iniciar Sesión';
            }
        });
    }
});