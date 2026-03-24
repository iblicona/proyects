<?php
require "conexion.php";

$action = $_GET["action"] ?? "";

/* Listar*/
if ($action === "listar") {
    $res = $conn->query("SELECT * FROM solicitudes ORDER BY id DESC");
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
}

/* Aprobar*/
if ($action === "aprobar") {

    $data = json_decode(file_get_contents("php://input"), true);
    $id   = $data["id"];

    // Obtener solicitud
    $res = $conn->query("SELECT * FROM solicitudes WHERE id=$id");
    $sol = $res->fetch_assoc();

    if (!$sol) {
        echo json_encode(["error" => "Solicitud no encontrada"]);
        exit;
    }

    // Crear renta automáticamente
    $equipos = json_encode([ $sol["equipo"] . " x" . $sol["cantidad"] ]);

    $stmt = $conn->prepare("INSERT INTO rentas
    (nombre, identificacion, equipos, aula, horas, hora_registro, usuario_registro)
    VALUES (?, ?, ?, ?, ?, NOW(), ?)");

    $stmt->bind_param(
        "ssssis",
        $sol["nombre"],
        $sol["tipo_usuario"],
        $equipos,
        $sol["aula"],
        $sol["horas"],
        $data["usuarioRegistro"]
    );

    $stmt->execute();

    // Actualizar estado
    $conn->query("UPDATE solicitudes SET estado='Aprobada' WHERE id=$id");

    echo json_encode(["ok" => true]);
}

/* Rechazar */
if ($action === "rechazar") {

    $data = json_decode(file_get_contents("php://input"), true);
    $id   = $data["id"];

    $conn->query("UPDATE solicitudes SET estado='Rechazada' WHERE id=$id");

    echo json_encode(["ok" => true]);
}