<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Tus credenciales de AWS
$host = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com";
$usuario = "axel";
$password = "admin1234";
$base_datos = "library";

// Esta inclusión es la que realmente abre la conexión $conn
include("/var/www/proyects/api/dbconection.php");

// Verificamos si la conexión existe como lo hace él
if (!isset($conn) || !$conn) {
    header('Content-Type: application/json');
    die(json_encode([
        "error" => "No se pudo establecer conexión con la base de datos"
    ]));
}

// Aseguramos el charset
$conn->set_charset("utf8");
?>