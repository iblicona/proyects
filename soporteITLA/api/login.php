<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require "conexion.php";

$data = json_decode(file_get_contents("php://input"), true);

$usuario = $data["usuario"] ?? "";
$password = md5($data["password"] ?? "");
$tipo = $data["tipo"] ?? "";

$stmt = $conn->prepare("SELECT usuario, tipo FROM usuarios WHERE usuario=? AND password=? AND tipo=?");
$stmt->bind_param("sss", $usuario, $password, $tipo);
$stmt->execute();

$stmt->store_result();

if ($stmt->num_rows > 0) {

    $stmt->bind_result($usuario_db, $tipo_db);
    $stmt->fetch();

    echo json_encode([
        "ok" => true,
        "usuario" => $usuario_db,
        "tipo" => $tipo_db
    ]);

} else {
    echo json_encode([
        "error" => "Credenciales incorrectas"
    ]);
}