/* ================================
   DATOS
================================ */

function obtenerRentas() {
    return JSON.parse(localStorage.getItem("rentas")) || [];
}

function guardarRentas(data) {
    localStorage.setItem("rentas", JSON.stringify(data));
}

let equiposSeleccionados = [];
/* ================================
   STOCK GENERAL
================================ */

let stock = JSON.parse(localStorage.getItem("stock")) || {
    "Laptop": 5,
    "Cañón": 3,
    "Bocinas": 4,
    "Cable HDMI": 10,
    "Cable Ethernet": 8,
    "Llaves": 6,
    "Extensión": 5
};

function guardarStock() {
    localStorage.setItem("stock", JSON.stringify(stock));
}

/* ================================
   SOLO SI EXISTE EL SELECT
================================ */

const equipoSelect = document.getElementById("equipoSelect");

if (equipoSelect) {

    equipoSelect.addEventListener("change", function() {

        const valor = this.value;
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
   AGREGAR EQUIPO
================================ */

function agregarEquipo() {

    const equipoBase = document.getElementById("equipoSelect").value;
    let equipoFinal = equipoBase;

    if (equipoBase === "Laptop") {
        const codigo = document.getElementById("codigoLaptop")?.value;
        if (!codigo) {
            alert("Debes ingresar el código de inventario");
            return;
        }
        equipoFinal = `Laptop (${codigo})`;
    }

    if (equipoBase === "Otro") {
        const otro = document.getElementById("otroEquipo")?.value;
        if (!otro) {
            alert("Debes especificar el equipo");
            return;
        }
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
                <button onclick="quitarEquipo(${index})">X</button>
            </li>
        `;
    });
}

function quitarEquipo(index) {
    equiposSeleccionados.splice(index, 1);
    actualizarLista();
}

/* ================================
   REGISTRAR RENTA
================================ */

function registrarRenta() {

    const nombre = document.getElementById("nombre").value;
    const idTipo = document.getElementById("idTipo").value;
    const aula = document.getElementById("aula").value;
    const horas = document.getElementById("horas").value;

    if (!nombre || !aula || !horas || equiposSeleccionados.length === 0) {
        alert("Faltan datos obligatorios");
        return;
    }

    const rentas = obtenerRentas();

    const ahora = new Date();
    const hora = ahora.toLocaleTimeString();

    rentas.push({
        id: rentas.length + 1,
        nombre,
        identificacion: idTipo,
        equipos: [...equiposSeleccionados],
        aula,
        horas,
        hora,
        estado: "Activa"
    });

    guardarRentas(rentas);

    alert("Renta registrada correctamente");

    equiposSeleccionados = [];
    actualizarLista();
    document.getElementById("formRenta").reset();
}

/* ================================
   TABLA
================================ */

function cargarTabla() {

    const tabla = document.getElementById("tablaRentas");
    if (!tabla) return;

    const rentas = obtenerRentas();

    tabla.innerHTML = "";

    rentas.forEach(r => {

        const equiposTexto = Array.isArray(r.equipos)
            ? r.equipos.join(", ")
            : (r.equipo || "Sin equipo");

        let fila = `
        <tr class="${r.estado === "Activa" ? "activa" : "devuelta"}">
            <td>${r.id}</td>
            <td>${r.nombre}</td>
            <td>${equiposTexto}</td>
            <td>${r.identificacion}</td>
            <td>${r.aula}</td>
            <td>${r.horas}</td>
            <td>${r.hora}</td>
            <td>${r.estado}</td>
            <td>
                <button onclick="marcarDevuelta(${r.id})">Devuelta</button>
            </td>
        </tr>
        `;

        tabla.innerHTML += fila;
    });
}

function marcarDevuelta(id) {

    let rentas = obtenerRentas();

    rentas = rentas.map(r => {

        if (r.id === id && r.estado === "Activa") {

            // Regresar stock
            if (Array.isArray(r.equipos)) {

                r.equipos.forEach(eq => {

                    const partes = eq.split(" x");
                    const nombreEquipo = partes[0];
                    const cantidad = parseInt(partes[1]) || 1;

                    if (stock[nombreEquipo] !== undefined) {
                        stock[nombreEquipo] += cantidad;
                    }
                });

                guardarStock();
            }

            r.estado = "Devuelta";
        }

        return r;
    });

    guardarRentas(rentas);
    cargarTabla();
}
/* ================================
   SOLICITUDES ONLINE
================================ */

function obtenerSolicitudes() {
    return JSON.parse(localStorage.getItem("solicitudes")) || [];
}

function guardarSolicitudes(data) {
    localStorage.setItem("solicitudes", JSON.stringify(data));
}

function cargarSolicitudes() {

    const tabla = document.getElementById("tablaSolicitudes");
    if (!tabla) return;

    const solicitudes = obtenerSolicitudes();
    tabla.innerHTML = "";

    solicitudes.forEach(s => {

        let fila = `
        <tr>
            <td>${s.nombre}</td>
            <td>${s.tipoUsuario}</td>
            <td>${s.equipo}</td>
            <td>${s.cantidad}</td>
            <td>${s.aula}</td>
            <td>${s.horas}</td>
            <td>${s.estado}</td>
            <td>
                ${s.estado === "Pendiente" ? `
                    <button onclick="aprobarSolicitud(${s.id})">Aprobar</button>
                    <button onclick="rechazarSolicitud(${s.id})">Rechazar</button>
                ` : ""}
            </td>
        </tr>
        `;

        tabla.innerHTML += fila;
    });
}

function aprobarSolicitud(id) {

    let solicitudes = obtenerSolicitudes();
    let rentas = obtenerRentas();

    const solicitud = solicitudes.find(s => s.id === id);

    if (!solicitud) return;

    if (solicitud.cantidad > stock[solicitud.equipo]) {
        alert("No hay suficiente stock disponible.");
        return;
    }

    // Descontar stock
    stock[solicitud.equipo] -= solicitud.cantidad;
    guardarStock();

    // Crear renta activa
    const ahora = new Date();
    const hora = ahora.toLocaleTimeString();

    rentas.push({
        id: rentas.length + 1,
        nombre: solicitud.nombre,
        identificacion: solicitud.tipoUsuario,
        equipos: [`${solicitud.equipo} x${solicitud.cantidad}`],
        aula: solicitud.aula,
        horas: solicitud.horas,
        hora,
        estado: "Activa"
    });

    guardarRentas(rentas);

    // Cambiar estado solicitud
    solicitud.estado = "Aprobada";
    guardarSolicitudes(solicitudes);

    cargarSolicitudes();
    cargarTabla();
}

function rechazarSolicitud(id) {

    let solicitudes = obtenerSolicitudes();

    solicitudes = solicitudes.map(s => {
        if (s.id === id) s.estado = "Rechazada";
        return s;
    });

    guardarSolicitudes(solicitudes);
    cargarSolicitudes();
}
/* ================================
   AJUSTE MANUAL DE INVENTARIO
================================ */

function mostrarAjusteStock() {

    const contenedor = document.getElementById("ajusteStock");
    const select = document.getElementById("equipoAjuste");

    contenedor.style.display =
        contenedor.style.display === "none" ? "block" : "none";

    select.innerHTML = "";

    Object.keys(stock).forEach(equipo => {
        select.innerHTML += `<option value="${equipo}">${equipo}</option>`;
    });
}

function aplicarAjuste() {

    const equipo = document.getElementById("equipoAjuste").value;
    const cantidad = parseInt(document.getElementById("cantidadAjuste").value);

    if (isNaN(cantidad)) {
        alert("Ingresa una cantidad válida.");
        return;
    }

    stock[equipo] += cantidad;

    if (stock[equipo] < 0) {
        stock[equipo] = 0;
    }

    guardarStock();
    cargarPanelStock();

    document.getElementById("cantidadAjuste").value = "";

    alert("Inventario actualizado correctamente.");
}