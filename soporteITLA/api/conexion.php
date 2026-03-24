<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com";
$usuario = "luisfer";
$password = "copcal";
$base_datos = "itla_rentas";

include("/var/www/proyects/soporteITLA/api/dbconection.php");

if (!isset($conn) || !$conn) {
    die(json_encode([
        "error" => "No se pudo establecer conexión con la base de datos"
    ]));
}

$conn->set_charset("utf8");

?>