const tabla = document.getElementById("tablaUsuarios");
const buscador = document.getElementById("buscador");

let fechaActual = new Date();

function obtenerUsuarios() {
  return JSON.parse(localStorage.getItem("usuariosBiblioteca")) || [];
}

function guardarUsuarios(usuarios) {
  localStorage.setItem("usuariosBiblioteca", JSON.stringify(usuarios));
}

function obtenerMesTexto(fecha) {
  const meses = [
    "Enero","Febrero","Marzo","Abril","Mayo","Junio",
    "Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"
  ];
  return meses[fecha.getMonth()] + " " + fecha.getFullYear();
}

function actualizarEtiquetaMes() {
  const etiqueta = document.getElementById("mesActual");
  if (etiqueta) {
    etiqueta.textContent = obtenerMesTexto(fechaActual);
  }
}

/* 🔹 CONTADOR DE USUARIOS DEL MES */
function actualizarContador(cantidad) {
  let contador = document.getElementById("contadorMes");
  if (!contador) return;
  contador.textContent = `Usuarios en el mes: ${cantidad}`;
}

/* 🔹 CARGAR USUARIOS POR MES + BUSCADOR */
function cargarUsuariosPorMes(filtro = "") {
  const usuarios = obtenerUsuarios();
  tabla.innerHTML = "";

  const mes = fechaActual.getMonth();
  const anio = fechaActual.getFullYear();

  const filtrados = usuarios.filter(user => {
    if (!user.fecha) return false;

    const partes = user.fecha.split("/");
    if (partes.length !== 3) return false;

    const fechaUser = new Date(partes[2], partes[1] - 1, partes[0]);

    const coincideMes =
      fechaUser.getMonth() === mes &&
      fechaUser.getFullYear() === anio;

    const coincideBusqueda =
      user.nombre.toLowerCase().includes(filtro) ||
      user.matricula.toLowerCase().includes(filtro) ||
      user.correo.toLowerCase().includes(filtro);

    return coincideMes && coincideBusqueda;
  });

  pintarTabla(filtrados);
  actualizarContador(filtrados.length);
}

/* 🔹 PINTAR TABLA */
function pintarTabla(lista) {
  lista.forEach(user => {
    tabla.innerHTML += `
      <tr>
        <td>${user.foto ? "📷" : "—"}</td>
        <td>${user.nombre}</td>
        <td>${user.matricula}</td>
        <td>${user.tipo}</td>
        <td>${user.area}</td>
        <td>${user.correo}</td>
        <td>${user.telefono}</td>
        <td>${user.fecha}</td>
        <td>
          <button onclick="editarUsuario(${user.id})">✏️</button>
          <button onclick="eliminarUsuario(${user.id})">🗑</button>
        </td>
      </tr>
    `;
  });
}

/* 🔹 MES ANTERIOR */
function mesAnterior() {
  fechaActual.setMonth(fechaActual.getMonth() - 1);
  actualizarEtiquetaMes();
  cargarUsuariosPorMes(buscador.value.toLowerCase());
}

/* 🔹 MES SIGUIENTE */
function mesSiguiente() {
  fechaActual.setMonth(fechaActual.getMonth() + 1);
  actualizarEtiquetaMes();
  cargarUsuariosPorMes(buscador.value.toLowerCase());
}

/* 🔹 ELIMINAR USUARIO */
function eliminarUsuario(id) {
  let usuarios = obtenerUsuarios();
  usuarios = usuarios.filter(user => user.id !== id);
  guardarUsuarios(usuarios);
  cargarUsuariosPorMes(buscador.value.toLowerCase());
}

/* 🔹 EDITAR USUARIO */
function editarUsuario(id) {
  const usuarios = obtenerUsuarios();
  const user = usuarios.find(u => u.id === id);

  const nuevoNombre = prompt("Editar nombre:", user.nombre);
  const nuevaArea = prompt("Editar área:", user.area);
  const nuevoCorreo = prompt("Editar correo:", user.correo);
  const nuevoTelefono = prompt("Editar teléfono:", user.telefono);

  if (nuevoNombre && nuevaArea && nuevoCorreo && nuevoTelefono) {
    user.nombre = nuevoNombre;
    user.area = nuevaArea;
    user.correo = nuevoCorreo;
    user.telefono = nuevoTelefono;
    guardarUsuarios(usuarios);
    cargarUsuariosPorMes(buscador.value.toLowerCase());
  }
}

/* 🔹 BUSCADOR EN TIEMPO REAL */
if (buscador) {
  buscador.addEventListener("keyup", () => {
    cargarUsuariosPorMes(buscador.value.toLowerCase());
  });
}

/* 🔹 EXPORTAR EXCEL DEL MES FILTRADO */
function exportarExcel() {
  const usuarios = obtenerUsuarios();

  const mes = fechaActual.getMonth();
  const anio = fechaActual.getFullYear();

  const filtrados = usuarios.filter(user => {
    if (!user.fecha) return false;
    const partes = user.fecha.split("/");
    const fechaUser = new Date(partes[2], partes[1] - 1, partes[0]);

    return (
      fechaUser.getMonth() === mes &&
      fechaUser.getFullYear() === anio
    );
  });

  if (filtrados.length === 0) {
    alert("No hay usuarios en este mes");
    return;
  }

  let tablaHTML = `
    <table border="1">
      <tr>
        <th colspan="7" style="font-size:18px;">
          Usuarios del mes: ${obtenerMesTexto(fechaActual)}
        </th>
      </tr>
      <tr>
        <th>Nombre</th>
        <th>Matrícula</th>
        <th>Tipo</th>
        <th>Área</th>
        <th>Correo</th>
        <th>Teléfono</th>
        <th>Fecha</th>
      </tr>
  `;

  filtrados.forEach(user => {
    tablaHTML += `
      <tr>
        <td>${user.nombre}</td>
        <td>${user.matricula}</td>
        <td>${user.tipo}</td>
        <td>${user.area}</td>
        <td>${user.correo}</td>
        <td>${user.telefono}</td>
        <td>${user.fecha}</td>
      </tr>
    `;
  });

  tablaHTML += `</table>`;

  const blob = new Blob([tablaHTML], {
    type: "application/vnd.ms-excel"
  });

  const url = URL.createObjectURL(blob);

  const a = document.createElement("a");
  a.href = url;
  a.download = "usuarios_mes.xls";
  a.click();

  URL.revokeObjectURL(url);
}

/* 🔹 INICIO */
document.addEventListener("DOMContentLoaded", () => {
  actualizarEtiquetaMes();
  cargarUsuariosPorMes();
});