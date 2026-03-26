const tabla = document.getElementById("tablaUsuarios");
const buscador = document.getElementById("buscador");

let fechaActual = new Date();
let usuariosGlobal = [];

// 🔹 OBTENER USUARIOS DESDE API
async function obtenerUsuarios() {
  try {
    const res = await fetch("./api/usuarios.php"); // ✅ FIX

    if (!res.ok) throw new Error("Error al obtener datos");

    const data = await res.json();

    console.log("Usuarios:", data); // 🔍 DEBUG

    // Convertir fecha SQL → dd/mm/yyyy
    usuariosGlobal = data.map(u => {
      const fecha = new Date(u.fecha);
      const dia = String(fecha.getDate()).padStart(2, "0");
      const mes = String(fecha.getMonth() + 1).padStart(2, "0");
      const anio = fecha.getFullYear();

      return {
        ...u,
        fecha: `${dia}/${mes}/${anio}`
      };
    });

  } catch (error) {
    console.error("Error cargando usuarios:", error);
  }
}

// 🔹 TEXTO DEL MES
function obtenerMesTexto(fecha) {
  const meses = [
    "Enero","Febrero","Marzo","Abril","Mayo","Junio",
    "Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"
  ];
  return meses[fecha.getMonth()] + " " + fecha.getFullYear();
}

// 🔹 ACTUALIZAR MES EN UI
function actualizarEtiquetaMes() {
  const etiqueta = document.getElementById("mesActual");
  if (etiqueta) {
    etiqueta.textContent = obtenerMesTexto(fechaActual);
  }
}

// 🔹 CONTADOR
function actualizarContador(cantidad) {
  let contador = document.getElementById("contadorMes");
  if (!contador) return;
  contador.textContent = `Usuarios en el mes: ${cantidad}`;
}

// 🔹 FILTRAR + MOSTRAR
function cargarUsuariosPorMes(filtro = "") {
  tabla.innerHTML = "";

  const mes = fechaActual.getMonth();
  const anio = fechaActual.getFullYear();

  const filtrados = usuariosGlobal.filter(user => {
    if (!user.fecha) return false;

    const partes = user.fecha.split("/");
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

// 🔹 PINTAR TABLA
function pintarTabla(lista) {
  lista.forEach(user => {
    tabla.innerHTML += `
      <tr>
        <td>
          ${user.foto 
            ? `<img src="./api/uploads/${user.foto}" width="40">` 
            : "—"}
        </td>
        <td>${user.nombre}</td>
        <td>${user.matricula}</td>
        <td>${user.tipo}</td>
        <td>${user.area || "—"}</td>
        <td>${user.correo}</td>
        <td>${user.telefono || "—"}</td>
        <td>${user.fecha}</td>
        <td>
          <button onclick="eliminarUsuario(${user.id})">🗑</button>
        </td>
      </tr>
    `;
  });
}

// 🔹 ELIMINAR USUARIO
async function eliminarUsuario(id) {
  if (!confirm("¿Eliminar usuario?")) return;

  try {
    const res = await fetch(`./api/usuarios.php?id=${id}`, {
      method: "DELETE"
    });

    const data = await res.json();

    if (data.status === "ok") {
      await init();
    } else {
      alert("Error al eliminar");
    }

  } catch (error) {
    console.error("Error eliminando:", error);
  }
}

// 🔹 BUSCADOR
if (buscador) {
  buscador.addEventListener("keyup", () => {
    cargarUsuariosPorMes(buscador.value.toLowerCase());
  });
}

// 🔹 EXPORTAR A EXCEL
function exportarExcel() {
  const filas = tabla.innerHTML;

  const blob = new Blob([`
    <table border="1">
      ${filas}
    </table>
  `], { type: "application/vnd.ms-excel" });

  const url = URL.createObjectURL(blob);

  const a = document.createElement("a");
  a.href = url;
  a.download = "usuarios.xls";
  a.click();

  URL.revokeObjectURL(url);
}

// 🔹 CAMBIAR MES
function mesAnterior() {
  fechaActual.setMonth(fechaActual.getMonth() - 1);
  actualizarEtiquetaMes();
  cargarUsuariosPorMes(buscador.value.toLowerCase());
}

function mesSiguiente() {
  fechaActual.setMonth(fechaActual.getMonth() + 1);
  actualizarEtiquetaMes();
  cargarUsuariosPorMes(buscador.value.toLowerCase());
}

// 🔹 INICIO
async function init() {
  await obtenerUsuarios();
  actualizarEtiquetaMes();
  cargarUsuariosPorMes();
}

document.addEventListener("DOMContentLoaded", init);