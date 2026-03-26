<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require "conexion.php";

$data = json_decode(file_get_contents("php://input"), true);

$usuario = $conn->real_escape_string($data['usuario']);
$password = $data['password'];

// Buscar por correo o matrícula
$sql = "SELECT * FROM usuarios 
        WHERE correo = '$usuario' 
        OR matricula = '$usuario'
        LIMIT 1";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

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
        echo json_encode(["status" => "error"]);
    }
} else {
    echo json_encode(["status" => "error"]);
}
?>