<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

require "conexion.php";

// 🔥 Obtener datos (JSON o POST)
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    $data = $_POST;
}

// Validar datos
if (!isset($data['usuario']) || !isset($data['password'])) {
    echo json_encode([
        "status" => "error",
        "msg" => "Datos incompletos"
    ]);
    exit;
}

$usuario = $conn->real_escape_string($data['usuario']);
$password = $data['password'];

// 🔍 Buscar usuario
$sql = "SELECT * FROM usuarios 
        WHERE correo = '$usuario' 
        OR matricula = '$usuario'
        LIMIT 1";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        "status" => "error",
        "msg" => "Error en consulta",
        "error" => $conn->error
    ]);
    exit;
}

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // 🔐 Verificar contraseña
    if (password_verify($password, $user['password'])) {
        echo json_encode([
            "status" => "ok",
            "user" => [
                "id" => $user["id"],
                "nombre" => $user["nombre"],
                "tipo" => $user["tipo"]
            ]
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "msg" => "Contraseña incorrecta"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "msg" => "Usuario no encontrado"
    ]);
}
?>