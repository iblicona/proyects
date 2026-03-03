let modoGuia = false;
let esperandoConfirmacionGuia = false;
let pasoGuia = 0;
let campoActivo = null; // 🔥 NUEVO: guarda el campo con focus

document.addEventListener("DOMContentLoaded", () => {

  const form = document.getElementById("formRegistro");
  if (!form) return;

  const correoInput = document.getElementById("correo");
  const botonRegistrar = form.querySelector("button");
  const chat = document.getElementById("iaChat");

  let correoValido = false;

  const horaInicial = chat.querySelector(".hora");
  if (horaInicial) horaInicial.textContent = obtenerHora();

  /* 📧 VALIDACIÓN DE CORREO */
  correoInput.addEventListener("input", function () {
    const correo = correoInput.value.trim().toLowerCase();

    if (correo.endsWith("@itla.edu.mx")) {
      correoValido = true;
      botonRegistrar.disabled = false;
      mostrarMensajeIA("✔ Tu correo institucional es válido. Cuando completes los demás campos podrás registrarte sin problema.");
    } else if (correo.includes("@")) {
      correoValido = false;
      botonRegistrar.disabled = true;
      mostrarMensajeIA("❌ Recuerda que debes usar tu correo institucional con terminación @itla.edu.mx.");
    } else {
      correoValido = false;
      botonRegistrar.disabled = true;
    }
  });

  /* 📩 ENVÍO DEL FORMULARIO */
  form.addEventListener("submit", function(e) {
    e.preventDefault();

    if (!correoValido) {
      alert("Debes usar un correo institucional válido.");
      return;
    }

    const nombre = document.getElementById("nombre").value.trim();
    const matricula = document.getElementById("matricula").value.trim();
    const tipo = document.getElementById("tipo").value;
    const area = document.getElementById("area").value.trim();
    const correo = correoInput.value.trim();
    const telefono = document.getElementById("telefono").value.trim();

    if (!nombre || !matricula || !tipo || !area || !correo || !telefono) {
      alert("Completa todos los campos obligatorios");
      return;
    }

    const usuarios = JSON.parse(localStorage.getItem("usuariosBiblioteca")) || [];

    usuarios.push({
      id: Date.now(),
      nombre,
      matricula,
      tipo,
      area,
      correo,
      telefono,
      fecha: new Date().toLocaleDateString()
    });

    localStorage.setItem("usuariosBiblioteca", JSON.stringify(usuarios));

    alert("Usuario registrado correctamente");
    form.reset();

    botonRegistrar.disabled = true;
    correoValido = false;

    mostrarMensajeIA("🎉 Tu registro se completó correctamente. Ahora ya puedes solicitar libros en la biblioteca.");
  });

  document.getElementById("preguntaIA").addEventListener("keydown", function(e) {
    if (e.key === "Enter") {
      e.preventDefault();
      responderIA();
    }
  });

  activarGuiaCampos();
});

/* 🕒 HORA */
function obtenerHora() {
  return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

/* 🔊 SONIDO */
function reproducirSonido() {
  const audio = document.getElementById("sonidoMensaje");
  if (audio) {
    audio.currentTime = 0;
    audio.play();
  }
}

/* 🤖 TOGGLE PANEL */
function toggleIA() {
  document.getElementById("iaPanel").classList.toggle("abierto");
}

/* 💬 MENSAJE IA */
function mostrarMensajeIA(texto) {
  const chat = document.getElementById("iaChat");

  chat.innerHTML += `
    <div class="ia-msg ia-bot">
      <div class="globo">${texto}</div>
      <div class="hora">${obtenerHora()}</div>
    </div>
  `;

  reproducirSonido();
  chat.scrollTop = chat.scrollHeight;
}

/* 🔍 NORMALIZAR */
function normalizar(texto) {
  return texto.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

/* ✔ CONFIRMACIONES */
function esSi(texto) {
  return /\b(si|sip|sii|claro|ok|vale|va|yes)\b/.test(texto);
}

function esNo(texto) {
  return /\b(no|nop|nel|nope)\b/.test(texto);
}

function quiereAvanzar(texto) {
  return /\b(listo|ya|hecho|siguiente|continuar|ok)\b/.test(texto);
}

/* 🤖 RESPUESTA IA */
function responderIA() {

  const input = document.getElementById("preguntaIA");
  const chat = document.getElementById("iaChat");
  const original = input.value.trim();
  let pregunta = normalizar(original);

  if (!pregunta) return;

  chat.innerHTML += `
    <div class="ia-msg ia-user">
      <div class="globo">${original}</div>
      <div class="hora">${obtenerHora()}</div>
    </div>
  `;

  let respuesta = generarRespuestaIA(pregunta);

  setTimeout(() => {
    mostrarMensajeIA(respuesta);
  }, 400);

  input.value = "";
}

/* 🧠 GENERADOR MEJORADO CON CONTEXTO */
function generarRespuestaIA(pregunta) {

  /* 🔥 RESPUESTA CONTEXTUAL SI DICE "¿QUÉ ES ESO?" */
  if (pregunta.includes("que es eso") || pregunta.includes("que significa")) {

    switch (campoActivo) {

      case "nombre":
        return "Debes escribir tu nombre completo tal como aparece en tu credencial institucional.";
      
      case "matricula":
        return "La matrícula es tu número de identificación dentro del ITLA. Está en tu credencial o portal académico.";
      
      case "tipo":
        return "Es tu rol dentro del ITLA: alumno, docente o administrativo.";
      
      case "area":
        return "Si eres alumno escribe tu carrera. Si eres docente o administrativo escribe tu departamento.";
      
      case "correo":
        return "Debes ingresar tu correo institucional con terminación @itla.edu.mx.";
      
      case "telefono":
        return "Es un número telefónico de contacto por si hay avisos o retrasos.";
      
      default:
        return "¿Sobre qué campo necesitas información?";
    }
  }

  /* CANCELAR GUÍA */
  if (modoGuia && esNo(pregunta)) {
    modoGuia = false;
    pasoGuia = 0;
    return "De acuerdo, cancelé la guía.";
  }

  /* INICIAR GUÍA */
  if (pregunta.includes("ayuda") || pregunta.includes("guiar") || pregunta.includes("llenar")) {
    esperandoConfirmacionGuia = true;
    return "¿Quieres que te guíe paso a paso?";
  }

  if (esperandoConfirmacionGuia && esSi(pregunta)) {
    iniciarGuia();
    return "Excelente 😊 " + textoPaso();
  }

  /* RESPUESTAS GENERALES */
  if (pregunta.match(/matri|numero escolar|id estudiante/)) {
    return "La matrícula es tu número de identificación institucional.";
  }

  if (pregunta.match(/hola|buenas|que tal/)) {
    return "Hola 👋 Puedo ayudarte con el registro o explicarte cualquier campo.";
  }

  /* MODO GUÍA */
  if (modoGuia) {

    if (quiereAvanzar(pregunta)) {
      pasoGuia++;
      return textoPaso();
    }

    return explicarPasoActual();
  }

  return "Puedo ayudarte con el registro o explicarte cualquier campo del formulario.";
}

/* 🧭 GUÍA */
function iniciarGuia() {
  modoGuia = true;
  esperandoConfirmacionGuia = false;
  pasoGuia = 1;
}

function textoPaso() {
  const pasos = {
    1: "Paso 1: Escribe tu nombre completo.",
    2: "Paso 2: Escribe tu matrícula.",
    3: "Paso 3: Selecciona tu tipo de usuario.",
    4: "Paso 4: Escribe tu carrera o área.",
    5: "Paso 5: Ingresa tu correo institucional.",
    6: "Paso 6: Escribe tu número telefónico.",
    7: "🎉 Ahora puedes presionar 'Registrar usuario'."
  };

  if (pasoGuia > 6) {
    modoGuia = false;
    pasoGuia = 0;
  }

  return pasos[pasoGuia];
}

function explicarPasoActual() {
  const explicaciones = {
    1: "Escribe tu nombre completo.",
    2: "Escribe tu matrícula.",
    3: "Selecciona tu tipo de usuario.",
    4: "Escribe tu carrera o departamento.",
    5: "Ingresa tu correo institucional.",
    6: "Escribe tu número telefónico."
  };

  return explicaciones[pasoGuia] + " Cuando termines escribe 'listo' para continuar.";
}

/* 🔥 DETECTAR CAMPO ACTIVO */
function activarGuiaCampos() {
  const campos = ["nombre","matricula","tipo","area","correo","telefono"];

  campos.forEach(id => {
    const elemento = document.getElementById(id);

    if (elemento) {
      elemento.addEventListener("focus", () => {
        campoActivo = id; // 🔥 Guarda el campo activo
      });
    }
  });
}