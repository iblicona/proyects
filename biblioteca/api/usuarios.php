<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, DELETE");

include("conexion.php");

// 🔥 ELIMINAR USUARIO
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    if (!isset($_GET['id'])) {
        echo json_encode(["status" => "error", "msg" => "ID requerido"]);
        exit;
    }

    $id = intval($_GET['id']);

    $sql = "DELETE FROM usuarios WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(["status" => "ok"]);
    } else {
        echo json_encode([
            "status" => "error",
            "msg" => $conn->error
        ]);
    }

    exit;
}

// 🔥 OBTENER USUARIOS
$sql = "SELECT * FROM usuarios ORDER BY fecha DESC";

$result = $conn->query($sql);

$usuarios = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
}

echo json_encode($usuarios);
?>