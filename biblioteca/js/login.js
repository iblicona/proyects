document.getElementById("loginForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const usuario  = document.getElementById("usuario").value.trim();
    const password = document.getElementById("password").value.trim();

    const btn = this.querySelector("button[type='submit']");
    btn.disabled = true;
    btn.textContent = "Verificando...";

    try {
        const response = await fetch("login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ usuario, password })
        });

        const data = await response.json();

        if (data.error) {
            alert(data.error);
            btn.disabled = false;
            btn.textContent = "Acceder al Sistema";
            return;
        }

        // Guardar sesión y rol detectado
        localStorage.setItem("user_id", data.id);
        localStorage.setItem("rol", data.rol);
        localStorage.setItem("nombre", data.nombre);

        window.location.href = "biblioteca.html"; 

    } catch (err) {
        alert("Error: No se pudo conectar con el servidor de AWS.");
        btn.disabled = false;
        btn.textContent = "Acceder al Sistema";
    }
});