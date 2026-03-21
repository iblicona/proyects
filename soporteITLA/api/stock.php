<?php
require "conexion.php";

$action = $_GET["action"] ?? "";

/* == LISTAR STOCK == */
if ($action === "listar") {

    $res = $conn->query("SELECT equipo, cantidad FROM equipos");

    $stock = [];

    while ($row = $res->fetch_assoc()) {
        $stock[$row["equipo"]] = $row["cantidad"];
    }

    echo json_encode($stock);
}

/* AJUSTAR STOCK */
if ($action === "ajustar") {

    $data = json_decode(file_get_contents("php://input"), true);

    $equipo   = $data["equipo"];
    $cantidad = $data["cantidad"];

    $conn->query("UPDATE equipos SET cantidad = cantidad + $cantidad WHERE equipo='$equipo'");

    $res = $conn->query("SELECT cantidad FROM equipos WHERE equipo='$equipo'");
    $row = $res->fetch_assoc();

    echo json_encode([
        "ok" => true,
        "nuevaCantidad" => $row["cantidad"]
    ]);
}