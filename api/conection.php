<?php

$host = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com";
$usuario = "admin";
$password = "Semillita1*";
$base_datos = "datos";

$conn = new mysqli($host, $usuario, $password, $base_datos);

if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "mensaje" => "Error de conexión a la base de datos"
    ]));
}

?>