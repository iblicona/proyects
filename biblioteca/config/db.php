<?php
// config/db.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/*
$host = "localhost";
$usuario = "root";
$password = "root";
$base_datos = "estoyharta";

$conn = new mysqli($host, $usuario, $password, $base_datos);
*/

// -------------------------------------------------------
// SERVIDOR 
// -------------------------------------------------------
$host = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com";
$usuario = "axel";
$password = "admin1234";
$base_datos = "estoyharta";

include("/var/www/proyects/api/dbconection.php");

if (!isset($conn) || !$conn) {
    die(json_encode([
        "error" => "No se pudo establecer conexión con la base de datos"
    ]));
}

// Usamos el charset que el profe prefiere
$conn->set_charset("utf8");
?>