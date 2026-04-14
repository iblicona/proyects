// js/login.js
document.getElementById("loginForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const usuario  = document.getElementById("usuario").value.trim();
    const password = document.getElementById("password").value.trim();
    const btn      = document.getElementById("btnLogin");
    const msgError = document.getElementById("msg-error");

    msgError.textContent = "";
    btn.disabled    = true;
    btn.textContent = "Verificando...";

    try {
        const response = await fetch("login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ usuario, password })
        });

        const data = await response.json();

        if (data.error) {
            msgError.textContent = "⚠ " + data.error;
            btn.disabled    = false;
            btn.textContent = "Acceder al Sistema";
            return;
        }

        // Guardar datos de sesión en localStorage
        localStorage.setItem("user_id", data.id);
        localStorage.setItem("rol",     data.rol);
        localStorage.setItem("nombre",  data.nombre);

        // Redirigir al panel
        window.location.href = "biblioteca.html";

    } catch (err) {
        msgError.textContent = "⚠ No se pudo conectar con el servidor.";
        btn.disabled    = false;
        btn.textContent = "Acceder al Sistema";
    }
});