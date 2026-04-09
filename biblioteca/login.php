<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexión estilo maestro (mysqli)
require "config/db.php"; 

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$correo = $data["usuario"] ?? "";
$password = $data["password"] ?? ""; 
$rol = $data["tipo"] ?? "";

// Consulta usando $conn de tu db.php
$stmt = $conn->prepare("SELECT id, nombre, rol FROM usuarios WHERE correo=? AND password=? AND rol=?");
$stmt->bind_param("sss", $correo, $password, $rol);
$stmt->execute();
$res = $stmt->get_result();

if ($user = $res->fetch_assoc()) {
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['rol'] = $user['rol'];
    $_SESSION['nombre'] = $user['nombre'];

    echo json_encode([
        "ok" => true,
        "id" => $user['id'],
        "nombre" => $user['nombre'],
        "rol" => $user['rol']
    ]);
} else {
    echo json_encode(["error" => "Credenciales incorrectas"]);
}