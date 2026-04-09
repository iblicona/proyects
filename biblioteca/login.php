<?php
require "config/db.php"; 
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$correo = $data["usuario"] ?? "";
$password = $data["password"] ?? ""; 

// IMPORTANTE: Busca por correo y contraseña tal cual vienen
$stmt = $conn->prepare("SELECT id, nombre, rol FROM usuarios WHERE correo=? AND password=?");
$stmt->bind_param("ss", $correo, $password);
$stmt->execute();
$res = $stmt->get_result();

if ($user = $res->fetch_assoc()) {
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['rol']     = $user['rol'];
    $_SESSION['nombre']  = $user['nombre'];

    echo json_encode([
        "ok" => true,
        "id" => $user['id'],
        "nombre" => $user['nombre'],
        "rol" => $user['rol']
    ]);
} else {
    // Si entra aquí es porque los datos no coinciden en la DB
    echo json_encode(["error" => "Correo o contraseña incorrectos"]);
}