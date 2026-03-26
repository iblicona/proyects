<?php

// 🔐 CONFIGURACIÓN DIRECTA
$host = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com";
$usuario = "axel";
$password = "admin1234";
$base_datos = "biblioteca_db";

// 🔗 IMPORTANTE (tu archivo SSL)
include("/var/www/proyects/api/dbconection.php");

// 🔌 CONEXIÓN
if (!isset($conn)) {

    $conn = new mysqli($host, $usuario, $password);

    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // 🔥 Opcional: forzar UTF-8
    $conn->set_charset("utf8mb4");
}
?>