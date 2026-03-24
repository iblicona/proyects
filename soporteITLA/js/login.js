document.getElementById("loginForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const usuario  = document.getElementById("usuario").value.trim();
    const password = document.getElementById("password").value.trim();
    const tipo     = document.getElementById("tipoUsuario").value;

    if (!usuario || !password) {
        alert("Ingresa usuario y contraseña");
        return;
    }

    const btn = this.querySelector("button[type='submit']");
    btn.disabled    = true;
    btn.textContent = "Verificando...";

    try {
        const response = await fetch("api/login.php", {
            method:  "POST",
            headers: { "Content-Type": "application/json" },
            body:    JSON.stringify({ usuario, password, tipo })
        });

        const data = await response.json();

        if (data.error) {
            alert("Error: " + data.error);
            btn.disabled    = false;
            btn.textContent = "Iniciar Sesión";
            return;
        }

        // Guardar sesión en sessionStorage (más seguro que localStorage)
        const sesion = {
            usuario: data.usuario,
            tipo:    data.tipo
        };

        sessionStorage.setItem("sesionActiva", JSON.stringify(sesion));

        // Redirección según rol
        if (data.tipo === "soporte") {
            window.location.href = "rentas.html";
        } else {
            window.location.href = "solicitud.html";
        }

    } catch (err) {
        alert("Error de conexión con el servidor. Verifica que Apache y PHP estén corriendo.");
        btn.disabled    = false;
        btn.textContent = "Iniciar Sesión";
    }
});
