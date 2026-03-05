<?php

$host = "endpoint-rds.amazonaws.com";
$usuario = "usuario_db";
$password = "password_db";
$base_datos = "nombre_db";

$conn = new mysqli($host, $usuario, $password, $base_datos);

if ($conn->connect_error) {
    die(json_encode([
        "status" => "error",
        "mensaje" => "Error de conexión a la base de datos"
    ]));
}

?>