<?php
// config/db.php
$host = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com";
$usuario = "axel";
$password = "admin1234";
$base_datos = "library";

// El archivo que realmente abre la conexión en el servidor de la escuela
include("/var/www/proyects/api/dbconection.php");

if (!$conn) {
    die("Error: No se pudo conectar a la base de datos.");
}
?>