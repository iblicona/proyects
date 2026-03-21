/* ================================
   EQUIPOS SELECCIONADOS (temporal)
================================ */

let equiposSeleccionados = [];

/* ================================
   SELECTOR DE EQUIPO (rentas.html)
================================ */

const equipoSelect = document.getElementById("equipoSelect");

if (equipoSelect) {

    equipoSelect.addEventListener("change", function () {

        const valor      = this.value;
        const campoExtra = document.getElementById("campoExtra");
        campoExtra.innerHTML = "";

        if (valor === "Laptop") {
            campoExtra.innerHTML = `
                <label>Código de Inventario</label>
                <input type="text" id="codigoLaptop" placeholder="Ej: EC-709">
            `;
        }

        if (valor === "Otro") {
            campoExtra.innerHTML = `
                <label>Especificar equipo</label>
                <input type="text" id="otroEquipo" placeholder="Describe el equipo">
            `;
        }
    });
}

/* ================================
   AGREGAR EQUIPO A LA LISTA
================================ */

function agregarEquipo() {

    const equipoBase = document.getElementById("equipoSelect").value;
    let equipoFinal  = equipoBase;

    if (equipoBase === "Laptop") {
        const codigo = document.getElementById("codigoLaptop")?.value;
        if (!codigo) { alert("Debes ingresar el código de inventario"); return; }
        equipoFinal = `Laptop (${codigo})`;
    }

    if (equipoBase === "Otro") {
        const otro = document.getElementById("otroEquipo")?.value;
        if (!otro) { alert("Debes especificar el equipo"); return; }
        equipoFinal = `Otro: ${otro}`;
    }

    equiposSeleccionados.push(equipoFinal);
    actualizarLista();
}

function actualizarLista() {

    const lista = document.getElementById("listaEquipos");
    if (!lista) return;

    lista.innerHTML = "";

    equiposSeleccionados.forEach((eq, index) => {
        lista.innerHTML += `
            <li>
                ${eq}
                <button type="button" onclick="quitarEquipo(${index})">X</button>
            </li>
        `;
    });
}

function quitarEquipo(index) {
    equiposSeleccionados.splice(index, 1);
    actualizarLista();
}

/* ================================
   REGISTRAR RENTA → API
================================ */

async function registrarRenta() {

    const nombre = document.getElementById("nombre").value;
    const idTipo = document.getElementById("idTipo").value;
    const aula   = document.getElementById("aula").value;
    const horas  = document.getElementById("horas").value;

    if (!nombre || !aula || !horas || equiposSeleccionados.length === 0) {
        alert("Faltan datos obligatorios");
        return;
    }

    const sesion          = JSON.parse(sessionStorage.getItem("sesionActiva"));
    const usuarioRegistro = sesion ? sesion.usuario : "Desconocido";

    try {
        const response = await fetch("api/rentas.php?action=registrar", {
            method:  "POST",
            headers: { "Content-Type": "application/json" },
            body:    JSON.stringify({
                nombre,
                identificacion: idTipo,
                equipos: [...equiposSeleccionados],
                aula,
                horas: parseInt(horas),
                usuario: usuarioRegistro
            })
        });

        const data = await response.json();

        if (data.error) {
            alert("Error: " + data.error);
            return;
        }

        alert("✅ Renta registrada correctamente");

        equiposSeleccionados = [];
        actualizarLista();
        document.getElementById("formRenta").reset();
        document.getElementById("campoExtra").innerHTML = "";

    } catch (err) {
        alert("Error de conexión: " + err.message);
    }
}

/* ================================
   CARGAR TABLA DE RENTAS → API
================================ */

async function cargarTabla() {

    const tabla = document.getElementById("tablaRentas");
    if (!tabla) return;

    try {
        const response = await fetch("api/rentas.php?action=listar");
        const rentas   = await response.json();

        tabla.innerHTML = "";

        rentas.forEach(r => {

            const equiposTexto = Array.isArray(r.equipos)
                ? r.equipos.join(", ")
                : (r.equipos || "Sin equipo");

            tabla.innerHTML += `
            <tr class="${r.estado === "Activa" ? "activa" : "devuelta"}">
                <td>${r.id}</td>
                <td>${r.nombre}</td>
                <td>${equiposTexto}</td>
                <td>${r.identificacion}</td>
                <td>${r.aula}</td>
                <td>${r.horas}</td>
                <td>${r.hora_registro}</td>
                <td>${r.usuario_registro || "N/A"}</td>
                <td>${r.estado}</td>
                <td>
                    ${r.estado === "Activa"
                        ? `<button onclick="marcarDevuelta(${r.id})">Devuelta</button>`
                        : "—"}
                </td>
            </tr>`;
        });

    } catch (err) {
        console.error("Error cargando rentas:", err);
    }
}

/* ================================
   MARCAR DEVUELTA → API
================================ */

async function marcarDevuelta(id) {

    if (!confirm("¿Confirmar devolución del equipo?")) return;

    try {
        const response = await fetch("api/rentas.php?action=devolver", {
            method:  "POST",
            headers: { "Content-Type": "application/json" },
            body:    JSON.stringify({ id })
        });

        const data = await response.json();

        if (data.error) {
            alert("Error: " + data.error);
            return;
        }

        cargarTabla();

    } catch (err) {
        alert("Error de conexión: " + err.message);
    }
}

/* ================================
   CARGAR SOLICITUDES → API
================================ */

async function cargarSolicitudes() {

    const tabla = document.getElementById("tablaSolicitudes");
    if (!tabla) return;

    try {
        const response    = await fetch("api/solicitudes.php?action=listar");
        const solicitudes = await response.json();

        tabla.innerHTML = "";

        solicitudes.forEach(s => {

            tabla.innerHTML += `
            <tr>
                <td>${s.nombre}</td>
                <td>${s.tipo_usuario}</td>
                <td>${s.equipo}</td>
                <td>${s.cantidad}</td>
                <td>${s.aula}</td>
                <td>${s.horas}</td>
                <td>${s.estado}</td>
                <td>
                    ${s.estado === "Pendiente" ? `
                        <button onclick="aprobarSolicitud(${s.id})">Aprobar</button>
                        <button onclick="rechazarSolicitud(${s.id})">Rechazar</button>
                    ` : "—"}
                </td>
            </tr>`;
        });

    } catch (err) {
        console.error("Error cargando solicitudes:", err);
    }
}

/* ================================
   APROBAR SOLICITUD → API
================================ */

async function aprobarSolicitud(id) {

    const sesion          = JSON.parse(sessionStorage.getItem("sesionActiva"));
    const usuarioRegistro = sesion ? sesion.usuario : "Soporte";

    try {
        const response = await fetch("api/solicitudes.php?action=aprobar", {
            method:  "POST",
            headers: { "Content-Type": "application/json" },
            body:    JSON.stringify({ id, usuarioRegistro })
        });

        const data = await response.json();

        if (data.error) {
            alert("Error: " + data.error);
            return;
        }

        cargarSolicitudes();
        cargarTabla();

    } catch (err) {
        alert("Error: " + err.message);
    }
}

/* ================================
   RECHAZAR SOLICITUD → API
================================ */

async function rechazarSolicitud(id) {

    try {
        const response = await fetch("api/solicitudes.php?action=rechazar", {
            method:  "POST",
            headers: { "Content-Type": "application/json" },
            body:    JSON.stringify({ id })
        });

        const data = await response.json();
        if (data.error) { alert("Error: " + data.error); return; }

        cargarSolicitudes();

    } catch (err) {
        alert("Error: " + err.message);
    }
}

/* ================================
   AJUSTE MANUAL DE STOCK
================================ */

async function mostrarAjusteStock() {

    const contenedor = document.getElementById("ajusteStock");
    const select     = document.getElementById("equipoAjuste");

    const visible = contenedor.style.display !== "none";
    contenedor.style.display = visible ? "none" : "block";

    if (!visible) {
        try {
            const response = await fetch("api/stock.php?action=listar");
            const stock    = await response.json();

            select.innerHTML = "";

            Object.keys(stock).forEach(equipo => {
                select.innerHTML += `<option value="${equipo}">${equipo} (${stock[equipo]} disponibles)</option>`;
            });

        } catch (err) {
            alert("Error cargando inventario: " + err.message);
        }
    }
}

async function aplicarAjuste() {

    const equipo   = document.getElementById("equipoAjuste").value;
    const cantidad = parseInt(document.getElementById("cantidadAjuste").value);

    if (isNaN(cantidad)) {
        alert("Ingresa una cantidad válida (puede ser negativa para restar).");
        return;
    }

    try {
        const response = await fetch("api/stock.php?action=ajustar", {
            method:  "POST",
            headers: { "Content-Type": "application/json" },
            body:    JSON.stringify({ equipo, cantidad })
        });

        const data = await response.json();

        if (data.error) {
            alert("Error: " + data.error);
            return;
        }

        alert(`✅ Inventario actualizado. ${equipo}: ${data.nuevaCantidad} unidades`);
        document.getElementById("cantidadAjuste").value = "";
        mostrarAjusteStock(); // Refrescar lista

    } catch (err) {
        alert("Error: " + err.message);
    }
}
