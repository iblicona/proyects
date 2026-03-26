document.getElementById("loginForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const usuario = document.getElementById("usuario").value.trim();
    const contrasena = document.getElementById("contrasena").value.trim();

    if (!usuario || !contrasena) {
        alert("Completa todos los campos");
        return;
    }

    try {
        const res = await fetch("./api/login.php", { // ✅ SOLO LOGIN
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

        console.log("Respuesta login:", data);

        if (data.status === "ok") {
            localStorage.setItem("usuario", JSON.stringify(data.user));
            window.location.href = "admin.html";
        } else {
            alert(data.msg || "Usuario o contraseña incorrectos");
        }

    } catch (error) {
        console.error("Error:", error);
        alert("Error de conexión con el servidor");
    }
});