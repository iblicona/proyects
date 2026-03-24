<?php

// 🔐 CONFIGURACIÓN DIRECTA
$DB_HOST = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com";
$DB_USER = "axel";
$DB_PASS = "admin1234";
$DB_NAME = "biblioteca_db";

// 🔗 IMPORTANTE (tu archivo SSL)
include("/var/www/proyects/api/dbconection.php");

// 🔌 CONEXIÓN
if (!isset($conn)) {

    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // 🔥 Opcional: forzar UTF-8
    $conn->set_charset("utf8mb4");
}
?>