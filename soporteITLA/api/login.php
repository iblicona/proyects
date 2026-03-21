<?php
require "conexion.php";

$data = json_decode(file_get_contents("php://input"), true);

$usuario = $data["usuario"] ?? "";
$password = md5($data["password"] ?? "");
$tipo = $data["tipo"] ?? "";

$stmt = $conn->prepare("SELECT usuario, tipo FROM usuarios WHERE usuario=? AND password=? AND tipo=?");
$stmt->bind_param("sss", $usuario, $password, $tipo);
$stmt->execute();

$res = $stmt->get_result();

if ($res->num_rows > 0) {

    $user = $res->fetch_assoc();

    echo json_encode([
        "ok" => true,
        "usuario" => $user["usuario"],
        "tipo" => $user["tipo"]
    ]);

} else {
    echo json_encode([
        "error" => "Credenciales incorrectas"
    ]);
}