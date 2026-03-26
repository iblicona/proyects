form.addEventListener("submit", async function(e) {
  e.preventDefault();

  if (!correoValido) {
    alert("Debes usar un correo institucional válido.");
    return;
  }

  const formData = new FormData();

  formData.append("nombre", document.getElementById("nombre").value.trim());
  formData.append("matricula", document.getElementById("matricula").value.trim());
  formData.append("tipo", document.getElementById("tipo").value);
  formData.append("area", document.getElementById("area").value.trim());
  formData.append("correo", document.getElementById("correo").value.trim());
  formData.append("telefono", document.getElementById("telefono").value.trim());

  const foto = document.getElementById("foto").files[0];
  if (foto) {
    formData.append("foto", foto);
  }

  try {
    const res = await fetch("/api/crear_usuario.php", {
      method: "POST",
      body: formData
    });

    const data = await res.json();

    if (data.status === "ok") {
      alert("Usuario registrado correctamente");
      form.reset();
      mostrarMensajeIA("🎉 Registro completado correctamente.");
    } else {
      alert("Error al registrar");
    }

  } catch (error) {
    console.error(error);
    alert("Error de conexión");
  }
});