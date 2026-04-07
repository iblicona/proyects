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
// 🔥 ACTUALIZAR USUARIO (PUT)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['id'])) {
        echo json_encode(["status" => "error", "msg" => "ID requerido"]);
        exit;
    }

    $id = intval($input['id']);
    $nombre = $conn->real_escape_string($input['nombre']);
    $tipo = $conn->real_escape_string($input['tipo']);
    $area = $conn->real_escape_string($input['area']);
    $correo = $conn->real_escape_string($input['correo']);
    $telefono = $conn->real_escape_string($input['telefono']);

    $sql = "UPDATE usuarios SET 
            nombre='$nombre', tipo='$tipo', area='$area', 
            correo='$correo', telefono='$telefono' 
            WHERE id = $id";

    if ($conn->query($sql)) {
        echo json_encode(["status" => "ok", "msg" => "Usuario actualizado"]);
    } else {
        echo json_encode(["status" => "error", "msg" => $conn->error]);
    }
    exit;
}