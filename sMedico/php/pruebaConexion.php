<?php
$host = "production.ccjgeakiwlqp.us-east-1.rds.amazonaws.com";
$usuario = "ian";
$password = "396925";
$bd = "ServicioMedicoITLA";
$conn = mysqli_connect($host, $usuario, $password, $bd);
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
echo "Conexión exitosa a la base de datos.";
?>