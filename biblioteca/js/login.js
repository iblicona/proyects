document.getElementById("loginForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const usuario  = document.getElementById("usuario").value.trim();
    const password = document.getElementById("password").value.trim();
    const tipo     = document.getElementById("tipoUsuario").value;

    const btn = this.querySelector("button[type='submit']");
    btn.disabled = true;
    btn.textContent = "Verificando...";

    try {
        // RUTA AJUSTADA: Directo a login.php
        const response = await fetch("login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ usuario, password, tipo })
        });

        const data = await response.json();

        if (data.error) {
            alert("Error: " + data.error);
            btn.disabled = false;
            btn.textContent = "Iniciar Sesión";
            return;
        }

        // Guardamos sesión
        localStorage.setItem("user_id", data.id);
        localStorage.setItem("rol", data.rol);
        localStorage.setItem("nombre", data.nombre);

        // Redirección al panel principal
        window.location.href = "biblioteca.html"; 

    } catch (err) {
        alert("Error de conexión con el servidor.");
        btn.disabled = false;
        btn.textContent = "Iniciar Sesión";
    }
});