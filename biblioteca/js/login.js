const form = document.getElementById("formRegistro");

// 🔍 Validación básica de correo institucional (puedes ajustar dominio)
let correoValido = true;

document.getElementById("correo").addEventListener("input", function () {
  const correo = this.value.trim();

  // 🔥 cambia el dominio si necesitas
  const regex = /^[a-zA-Z0-9._%+-]+@(itla\.edu\.mx)$/;

  if (!regex.test(correo)) {
    this.style.border = "2px solid red";
    correoValido = false;
  } else {
    this.style.border = "2px solid green";
    correoValido = true;
  }
});

// 🚀 ENVÍO DEL FORMULARIO
form.addEventListener("submit", async function (e) {
  e.preventDefault();

  // 🔍 Obtener valores
  const nombre = document.getElementById("nombre").value.trim();
  const matricula = document.getElementById("matricula").value.trim();
  const tipo = document.getElementById("tipo").value;
  const area = document.getElementById("area").value.trim();
  const correo = document.getElementById("correo").value.trim();
  const telefono = document.getElementById("telefono").value.trim();
  const password = document.getElementById("password") 
      ? document.getElementById("password").value.trim() 
      : "";

  // 🔍 Validaciones
  if (!nombre || !matricula || !tipo || !correo) {
    alert("Completa los campos obligatorios");
    return;
  }

  if (!correoValido) {
    alert("Debes usar un correo institucional válido.");
    return;
  }

  // 📦 FormData
  const formData = new FormData();
  formData.append("nombre", nombre);
  formData.append("matricula", matricula);
  formData.append("tipo", tipo);
  formData.append("area", area);
  formData.append("correo", correo);
  formData.append("telefono", telefono);

  if (password) {
    formData.append("password", password);
  }

  // 🖼️ Imagen
  const fotoInput = document.getElementById("foto");
  if (fotoInput && fotoInput.files.length > 0) {
    formData.append("foto", fotoInput.files[0]);
  }

  try {
    const res = await fetch("./api/login.php", {
      method: "POST",
      body: formData
    });

    // 🔍 Validar respuesta
    if (!res.ok) {
      throw new Error("Respuesta HTTP inválida");
    }

    const data = await res.json();

    console.log("Respuesta servidor:", data); // DEBUG

    if (data.status === "ok") {
      alert("✅ Usuario registrado correctamente");
      form.reset();

      // limpiar estilos
      document.getElementById("correo").style.border = "";

      if (typeof mostrarMensajeIA === "function") {
        mostrarMensajeIA("🎉 Registro completado correctamente.");
      }

    } else {
      alert("❌ " + (data.msg || "Error al registrar"));
    }

  } catch (error) {
    console.error("Error:", error);
    alert("❌ Error de conexión con el servidor");
  }
});