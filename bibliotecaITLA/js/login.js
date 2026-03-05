document.getElementById("loginForm").addEventListener("submit", function(e) {
    e.preventDefault(); // Evita que se recargue la página

    const usuario = document.getElementById("usuario").value;
    const contrasena = document.getElementById("contrasena").value;

    // 🔐 Credenciales del admin
    const adminUser = "admin";
    const adminPass = "12345";

    if (usuario === adminUser && contrasena === adminPass) {
        // Si es correcto → entra al panel admin
        window.location.href = "admin.html";
    } else {
        // ❌ Ventana emergente típica
        alert("Usuario o contraseña incorrectos");
    }
});