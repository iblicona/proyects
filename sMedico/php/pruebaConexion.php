<?php
$host = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com";
$usuario = "ian";
$password = "396925";
$base_datos = "ServicioMedicoITLA";
include ("/var/www/proyects/api/dbconection.php");
$conn = mysqli_connect($host, $usuario, $password, $base_datos);
echo "Conexión exitosa a la base de datos.";
?>