<?php
header("Content-Type: application/json");

$host = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com";
$usuario = "luisfer";
$password = "copcal";
$base_datos = "itla_rentas";

// Conexión correcta
$conn = new mysqli($host, $usuario, $password, $base_datos);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "error" => "Error de conexión: " . $conn->connect_error
    ]);
    exit;
}

$conn->set_charset("utf8");

echo json_encode([
    "success" => true,
    "message" => "Conexión exitosa"
]);
?>