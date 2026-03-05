/* =========================================================
   registro.js  –  Validación de correo + Asistente IA
   ========================================================= */

let modoGuia = false;
let esperandoConfirmacionGuia = false;
let pasoGuia = 0;
let campoActivo = null;

document.addEventListener("DOMContentLoaded", () => {

  const correoInput  = document.getElementById("correo");
  const btnRegistrar = document.getElementById("btnRegistrar");
  const chat         = document.getElementById("iaChat");

  // Mostrar hora en el mensaje inicial
  const horaInicial = chat.querySelector(".hora");
  if (horaInicial) horaInicial.textContent = obtenerHora();

  /* ── Validación de correo en tiempo real ─────────────────── */
  if (correoInput) {
    correoInput.addEventListener("input", function () {
      const correo   = correoInput.value.trim().toLowerCase();
      const feedback = document.getElementById("correoFeedback");

      if (correo.endsWith("@itla.edu.mx")) {
        if (feedback) { feedback.textContent = "✔ Correo institucional válido."; feedback.className = "feedback ok"; }
        mostrarMensajeIA("✔ Tu correo institucional es válido. Cuando completes los demás campos podrás registrarte sin problema.");
      } else if (correo.includes("@")) {
        if (feedback) { feedback.textContent = "❌ Usa tu correo @itla.edu.mx."; feedback.className = "feedback error"; }
        mostrarMensajeIA("❌ Recuerda que debes usar tu correo institucional con terminación @itla.edu.mx.");
      } else {
        if (feedback) { feedback.textContent = ""; feedback.className = "feedback"; }
      }
    });
  }

  /* ── Enter en el chat ─────────────────────────────────────── */
  document.getElementById("preguntaIA").addEventListener("keydown", function (e) {
    if (e.key === "Enter") { e.preventDefault(); responderIA(); }
  });

  activarGuiaCampos();
});

/* ── Utilidades ─────────────────────────────────────────────── */
function obtenerHora() {
  return new Date().toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

function reproducirSonido() {
  const audio = document.getElementById("sonidoMensaje");
  if (audio) { audio.currentTime = 0; audio.play().catch(() => {}); }
}

function toggleIA() {
  const panel = document.getElementById("iaPanel");
  panel.classList.toggle("abierto");
  document.body.classList.toggle("chat-abierto");
}

function mostrarMensajeIA(texto) {
  const chat = document.getElementById("iaChat");
  chat.innerHTML += `
    <div class="ia-msg ia-bot">
      <div class="globo">${texto}</div>
      <div class="hora">${obtenerHora()}</div>
    </div>`;
  reproducirSonido();
  chat.scrollTop = chat.scrollHeight;
}

function normalizar(texto) {
  return texto.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

function esSi(t)        { return /\b(si|sip|sii|claro|ok|vale|va|yes)\b/.test(t); }
function esNo(t)        { return /\b(no|nop|nel|nope)\b/.test(t); }
function quiereAvanzar(t){ return /\b(listo|ya|hecho|siguiente|continuar|ok)\b/.test(t); }

/* ── Respuesta IA ────────────────────────────────────────────── */
function responderIA() {
  const input    = document.getElementById("preguntaIA");
  const chat     = document.getElementById("iaChat");
  const original = input.value.trim();
  const pregunta = normalizar(original);

  if (!pregunta) return;

  chat.innerHTML += `
    <div class="ia-msg ia-user">
      <div class="globo">${original}</div>
      <div class="hora">${obtenerHora()}</div>
    </div>`;

  const respuesta = generarRespuestaIA(pregunta);
  setTimeout(() => mostrarMensajeIA(respuesta), 400);

  input.value = "";
}

function generarRespuestaIA(pregunta) {

  // Contexto del campo activo
  if (pregunta.includes("que es eso") || pregunta.includes("que significa")) {
    const ctx = {
      nombre:    "Escribe tu nombre completo tal como aparece en tu credencial institucional.",
      matricula: "La matrícula es tu número de identificación en el ITLA. Encuéntrala en tu credencial o portal académico.",
      tipo:      "Es tu rol dentro del ITLA: Alumno, Docente o Administrativo.",
      area:      "Si eres alumno escribe tu carrera; si eres docente o administrativo, tu departamento.",
      correo:    "Debes ingresar tu correo institucional con terminación @itla.edu.mx.",
      telefono:  "Un número de contacto para avisos o notificaciones de la biblioteca.",
    };
    return ctx[campoActivo] || "¿Sobre qué campo necesitas información?";
  }

  // Cancelar guía
  if (modoGuia && esNo(pregunta)) {
    modoGuia = false; pasoGuia = 0;
    return "De acuerdo, cancelé la guía.";
  }

  // Iniciar guía
  if (pregunta.includes("ayuda") || pregunta.includes("guiar") || pregunta.includes("llenar")) {
    esperandoConfirmacionGuia = true;
    return "¿Quieres que te guíe paso a paso?";
  }

  if (esperandoConfirmacionGuia && esSi(pregunta)) {
    iniciarGuia();
    return "Excelente 😊 " + textoPaso();
  }

  if (pregunta.match(/matri|numero escolar|id estudiante/)) {
    return "La matrícula es tu número de identificación institucional.";
  }

  if (pregunta.match(/hola|buenas|que tal/)) {
    return "Hola 👋 Puedo ayudarte con el registro o explicarte cualquier campo.";
  }

  // Modo guía activo
  if (modoGuia) {
    if (quiereAvanzar(pregunta)) { pasoGuia++; return textoPaso(); }
    return explicarPasoActual();
  }

  return "Puedo ayudarte con el registro o explicarte cualquier campo del formulario.";
}

function iniciarGuia() {
  modoGuia = true; esperandoConfirmacionGuia = false; pasoGuia = 1;
}

function textoPaso() {
  const pasos = {
    1: "Paso 1: Escribe tu nombre completo.",
    2: "Paso 2: Escribe tu matrícula.",
    3: "Paso 3: Selecciona tu tipo de usuario.",
    4: "Paso 4: Escribe tu carrera o área.",
    5: "Paso 5: Ingresa tu correo institucional (@itla.edu.mx).",
    6: "Paso 6: Escribe tu número telefónico.",
    7: "🎉 ¡Listo! Ahora presiona 'Registrar usuario'.",
  };
  if (pasoGuia > 6) { modoGuia = false; pasoGuia = 0; }
  return pasos[pasoGuia] || "¡Registro completado!";
}

function explicarPasoActual() {
  const exp = {
    1: "Escribe tu nombre completo.",
    2: "Escribe tu matrícula.",
    3: "Selecciona tu tipo de usuario.",
    4: "Escribe tu carrera o departamento.",
    5: "Ingresa tu correo institucional.",
    6: "Escribe tu número telefónico.",
  };
  return (exp[pasoGuia] || "") + " Cuando termines escribe 'listo' para continuar.";
}

function activarGuiaCampos() {
  ["nombre","matricula","tipo","area","correo","telefono"].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener("focus", () => { campoActivo = id; });
  });
}
