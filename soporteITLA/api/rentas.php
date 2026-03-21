<?php
require "conexion.php";

$action = $_GET["action"] ?? "";

if ($action === "registrar") {

    $data = json_decode(file_get_contents("php://input"), true);

    $stmt = $conn->prepare("INSERT INTO rentas
    (nombre, identificacion, equipos, aula, horas, hora_registro, usuario_registro)
    VALUES (?, ?, ?, ?, ?, NOW(), ?)");

    $equipos = json_encode($data["equipos"]);

    $stmt->bind_param(
        "ssssis",
        $data["nombre"],
        $data["identificacion"],
        $equipos,
        $data["aula"],
        $data["horas"],
        $data["usuario"]
    );

    $stmt->execute();

    echo json_encode(["ok" => true]);
}

if ($action === "listar") {

    $res = $conn->query("SELECT * FROM rentas ORDER BY id DESC");
    $data = [];

    while ($row = $res->fetch_assoc()) {
        $row["equipos"] = json_decode($row["equipos"], true);
        $data[] = $row;
    }

    echo json_encode($data);
}

if ($action === "devolver") {

    $data = json_decode(file_get_contents("php://input"), true);
    $id   = $data["id"];

    $conn->query("UPDATE rentas SET estado='Devuelta' WHERE id=$id");

    echo json_encode(["ok" => true]);
}