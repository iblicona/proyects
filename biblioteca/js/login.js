document.getElementById("loginForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const usuario = document.getElementById("usuario").value;
    const contrasena = document.getElementById("contrasena").value;

    try {
        const res = await fetch("/api/login.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                usuario: usuario,
                password: contrasena
            })
        });

        const data = await res.json();

        if (data.status === "ok") {
            // Guardar sesión
            localStorage.setItem("usuario", JSON.stringify(data.user));

            // Redirigir
            window.location.href = "admin.html";
        } else {
            alert("Usuario o contraseña incorrectos");
        }

    } catch (error) {
        console.error(error);
        alert("Error de conexión con el servidor");
    }
});