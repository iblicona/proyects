<?php
$host = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com";
$usuario = "luisfer";
$password = "copcal";
$base_datos = "itla_rentas";

$conn = new mysqli($host, $usuario, $password, $base_datos);

if ($conn->connect_error) {
    die(json_encode([
        "error" => "Error de conexión: " . $conn->connect_error
    ]));
}

$conn->set_charset("utf8");
?>